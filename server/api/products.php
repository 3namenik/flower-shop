<?php

function list_categories(): void
{
    json(db()->query('SELECT * FROM categories ORDER BY name')->fetchAll());
}

function list_products(): void
{
    $sql = "
        SELECT p.*, c.name AS category_name, s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN suppliers  s ON s.id = p.supplier_id
        ORDER BY p.id DESC
    ";
    json(db()->query($sql)->fetchAll());
}

function create_product(): void
{
    $b = body();
    if (empty($b['name']) || !isset($b['price'])) {
        json(['error' => 'name и price обязательны'], 400);
    }

    $stmt = db()->prepare("
        INSERT INTO products (name, category_id, supplier_id, color, description, price, stock_qty)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $b['name'],
        $b['category_id'] ?? null,
        $b['supplier_id'] ?? null,
        $b['color']       ?? null,
        $b['description'] ?? null,
        $b['price'],
        $b['stock_qty']   ?? 0,
    ]);

    json(['id' => (int) db()->lastInsertId()], 201);
}

function delete_product(int $id): void
{
    try {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        http_response_code(204);
        exit;
    } catch (PDOException) {
        json(['error' => 'Товар используется в заказах'], 400);
    }
}

function update_product(int $id): void
{
    $b = body();
    if (empty($b['name']) || !isset($b['price'])) {
        json(['error' => 'name и price обязательны'], 400);
    }

    $stmt = db()->prepare("
        UPDATE products
        SET name = ?, category_id = ?, supplier_id = ?, color = ?,
            description = ?, price = ?, stock_qty = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $b['name'],
        $b['category_id'] ?? null,
        $b['supplier_id'] ?? null,
        $b['color']       ?? null,
        $b['description'] ?? null,
        $b['price'],
        $b['stock_qty']   ?? 0,
        $id,
    ]);

    json(['ok' => true]);
}