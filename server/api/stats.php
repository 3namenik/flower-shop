<?php

$pdo = db();

$products  = (int)   $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$customers = (int)   $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$orders    = (int)   $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue   = (float) $pdo->query(
    "SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'доставлен'"
)->fetchColumn();

$lowStock = $pdo->query("
    SELECT id, name, color, stock_qty
    FROM products
    WHERE stock_qty < 30
    ORDER BY stock_qty
")->fetchAll();

json([
    'products'  => $products,
    'customers' => $customers,
    'orders'    => $orders,
    'revenue'   => $revenue,
    'lowStock'  => $lowStock,
]);