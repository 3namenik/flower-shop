<?php

function list_customers(): void
{
    $rows = db()->query("
        SELECT c.*,
               COUNT(o.id)                     AS orders_count,
               COALESCE(SUM(o.total_price), 0) AS total_spent
        FROM customers c
        LEFT JOIN orders o ON o.customer_id = c.id
        GROUP BY c.id
        ORDER BY c.id DESC
    ")->fetchAll();

    json($rows);
}

function create_customer(): void
{
    $b = body();
    if (empty($b['full_name'])) {
        json(['error' => 'full_name обязателен'], 400);
    }

    db()->prepare(
        'INSERT INTO customers (full_name, phone, email, birth_date) VALUES (?, ?, ?, ?)'
    )->execute([
        $b['full_name'],
        !empty($b['phone'])      ? $b['phone']      : null,
        !empty($b['email'])      ? $b['email']      : null,
        !empty($b['birth_date']) ? $b['birth_date'] : null,
    ]);

    json(['id' => (int) db()->lastInsertId()], 201);
}