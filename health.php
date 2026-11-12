<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/includes/database.php';
try {
    connect_database()->query('SELECT id, category, price, stock FROM products LIMIT 1');
    echo json_encode(['status' => 'ok']);
} catch (Throwable $error) {
    http_response_code(503);
    // No credentials, hostnames or SQL errors in a public endpoint.
    echo json_encode(['status' => 'unavailable']);
}
