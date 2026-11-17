<?php

function list_orders(): void
{
    $orders = db()->query("
        SELECT o.*, c.full_name AS customer_name
        FROM orders o
        JOIN customers c ON c.id = o.customer_id
        ORDER BY o.order_date DESC
    ")->fetchAll();

    $itemStmt = db()->prepare("
        SELECT oi.*, p.name AS product_name, p.color
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");

    foreach ($orders as &$o) {
        $itemStmt->execute([$o['id']]);
        $o['items'] = $itemStmt->fetchAll();
    }

    json($orders);
}

function create_order(): void
{
    $b = body();
    if (empty($b['customer_id']) || empty($b['items']) || !is_array($b['items'])) {
        json(['error' => 'Нужен клиент и хотя бы один товар'], 400);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // 1. Проверяем остатки (с блокировкой строк, чтобы никто не списал параллельно)
        $lockStmt   = $pdo->prepare('SELECT name, stock_qty FROM products WHERE id = ? FOR UPDATE');
        $deductStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?');

        foreach ($b['items'] as $it) {
            $lockStmt->execute([$it['product_id']]);
            $prod = $lockStmt->fetch();

            if (!$prod) {
                throw new Exception("Товар id={$it['product_id']} не найден");
            }
            if ($prod['stock_qty'] < $it['quantity']) {
                throw new Exception(
                    "Недостаточно товара «{$prod['name']}»: "
                    . "в наличии {$prod['stock_qty']}, нужно {$it['quantity']}"
                );
            }
        }

        // 2. Создаём заказ
        $stmt = $pdo->prepare(
            'INSERT INTO orders (customer_id, delivery_addr, comment) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $b['customer_id'],
            $b['delivery_addr'] ?? null,
            $b['comment']       ?? null,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        // 3. Добавляем позиции и списываем остаток
        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
        );
        foreach ($b['items'] as $it) {
            $itemStmt->execute([
                $orderId,
                $it['product_id'],
                $it['quantity'],
                $it['unit_price'],
            ]);
            $deductStmt->execute([$it['quantity'], $it['product_id']]);
        }

        $pdo->commit();
        json(['id' => $orderId], 201);

    } catch (Throwable $e) {
        $pdo->rollBack();
        json(['error' => $e->getMessage()], 400);   // 400 — это бизнес-ошибка, не 500
    }
}



function update_order_status(int $id): void
{
    $b = body();
    $newStatus = $b['status'] ?? null;

    if (!in_array($newStatus, ['новый', 'в работе', 'доставлен', 'отменён'], true)) {
        json(['error' => 'Некорректный статус'], 400);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // 1. Блокируем заказ и смотрим старый статус
        $cur = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
        $cur->execute([$id]);
        $oldStatus = $cur->fetchColumn();

        if ($oldStatus === false) {
            throw new Exception('Заказ не найден');
        }

        if ($oldStatus === $newStatus) {
            $pdo->commit();
            json(['ok' => true, 'changed' => false]);
        }

        // 2. Забираем состав заказа
        $itemsStmt = $pdo->prepare(
            'SELECT product_id, quantity FROM order_items WHERE order_id = ?'
        );
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        $wasCancelled = ($oldStatus === 'отменён');
        $nowCancelled = ($newStatus === 'отменён');

        if (!$wasCancelled && $nowCancelled) {
            // ─── отмена: возвращаем на склад
            $restock = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?'
            );
            foreach ($items as $it) {
                $restock->execute([$it['quantity'], $it['product_id']]);
            }

        } elseif ($wasCancelled && !$nowCancelled) {
            // ─── восстановление из отмены: снова проверяем и списываем
            $lockStmt   = $pdo->prepare('SELECT name, stock_qty FROM products WHERE id = ? FOR UPDATE');
            $deductStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?');

            foreach ($items as $it) {
                $lockStmt->execute([$it['product_id']]);
                $prod = $lockStmt->fetch();

                if (!$prod || $prod['stock_qty'] < $it['quantity']) {
                    $name  = $prod['name'] ?? "id={$it['product_id']}";
                    $have  = $prod['stock_qty'] ?? 0;
                    throw new Exception(
                        "Недостаточно товара «{$name}»: в наличии {$have}, нужно {$it['quantity']}"
                    );
                }
                $deductStmt->execute([$it['quantity'], $it['product_id']]);
            }
        }
        // остальные переходы (новый → в работе → доставлен) склад не трогают

        // 3. Обновляем статус
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')
            ->execute([$newStatus, $id]);

        $pdo->commit();
        json(['ok' => true, 'changed' => true]);

    } catch (Throwable $e) {
        $pdo->rollBack();
        json(['error' => $e->getMessage()], 400);
    }
}