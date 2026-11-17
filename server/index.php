<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

function json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function body(): array
{
    $raw = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : [];
    return is_array($data) ? $data : [];
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

try {
    // ---------- статистика
    if ($path === '/api/stats' && $method === 'GET') {
        require __DIR__ . '/api/stats.php';
    }

    // ---------- товары
    elseif ($path === '/api/products/categories' && $method === 'GET') {
        require __DIR__ . '/api/products.php';
        list_categories();
    }
    elseif ($path === '/api/products' && $method === 'GET') {
        require __DIR__ . '/api/products.php';
        list_products();
    }
    elseif ($path === '/api/products' && $method === 'POST') {
        require __DIR__ . '/api/products.php';
        create_product();
    }
    elseif (preg_match('#^/api/products/(\d+)$#', $path, $m) && $method === 'DELETE') {
        require __DIR__ . '/api/products.php';
        delete_product((int) $m[1]);
    }
    elseif (preg_match('#^/api/products/(\d+)$#', $path, $m) && $method === 'PATCH') {
        require __DIR__ . '/api/products.php';
        update_product((int) $m[1]);
    }

    // ---------- заказы
    elseif ($path === '/api/orders' && $method === 'GET') {
        require __DIR__ . '/api/orders.php';
        list_orders();
    }
    elseif ($path === '/api/orders' && $method === 'POST') {
        require __DIR__ . '/api/orders.php';
        create_order();
    }
    elseif (preg_match('#^/api/orders/(\d+)/status$#', $path, $m) && $method === 'PATCH') {
        require __DIR__ . '/api/orders.php';
        update_order_status((int) $m[1]);
    }

    // ---------- клиенты
    elseif ($path === '/api/customers' && $method === 'GET') {
        require __DIR__ . '/api/customers.php';
        list_customers();
    }
    elseif ($path === '/api/customers' && $method === 'POST') {
        require __DIR__ . '/api/customers.php';
        create_customer();
    }

    else {
        json(['error' => 'Not found'], 404);
    }
} catch (Throwable $e) {
    json(['error' => $e->getMessage()], 500);
}