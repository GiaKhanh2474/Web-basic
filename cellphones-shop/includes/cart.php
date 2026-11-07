<?php
declare(strict_types=1);
require_once __DIR__ . '/catalog.php';

function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_keys($cart);
    $query = db()->prepare('SELECT * FROM products WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $query->execute($ids);
    $items = [];
    $clean = [];
    foreach ($query->fetchAll() as $product) {
        $quantity = min((int) $cart[$product['id']], (int) $product['stock'], 99);
        if ($quantity > 0) {
            $clean[(int) $product['id']] = $quantity;
            $product['quantity'] = $quantity;
            $product['line_total'] = (int) $product['price'] * $quantity;
            $items[] = $product;
        }
    }
    $_SESSION['cart'] = $clean;
    return $items;
}

function cart_count(): int
{
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}

function cart_total(): int
{
    return (int) array_sum(array_column(cart_items(), 'line_total'));
}
