<?php
declare(strict_types=1);
require_once __DIR__ . '/catalog.php';

function shop_categories(): array
{
    return [
        'monitor' => ['label' => 'Màn hình', 'icon' => 'monitor', 'anchor' => 'man-hinh', 'description' => 'Sắc nét từng chi tiết, rộng mở mọi cảm hứng.'],
        'pc' => ['label' => 'Máy tính', 'icon' => 'tower', 'anchor' => 'may-tinh', 'description' => 'Sức mạnh cho góc làm việc và giải trí của bạn.'],
        'laptop' => ['label' => 'Laptop', 'icon' => 'laptop', 'anchor' => 'laptop', 'description' => 'Mang theo công việc và ý tưởng đến mọi nơi.'],
    ];
}

function category_from_product(array $product): string
{
    $category = (string) ($product['category'] ?? 'monitor');
    return isset(shop_categories()[$category]) ? $category : 'monitor';
}

function product_specs(array $product): array
{
    $category = category_from_product($product);
    $specs = ['Thương hiệu' => (string) ($product['brand'] ?? '')];
    if ($category !== 'monitor') {
        foreach (['Bộ xử lý' => 'cpu', 'Bộ nhớ RAM' => 'ram', 'Ổ lưu trữ' => 'storage', 'Đồ họa' => 'gpu'] as $label => $field) {
            if (trim((string) ($product[$field] ?? '')) !== '') $specs[$label] = (string) $product[$field];
        }
    }
    if ($category !== 'pc') {
        if ((float) ($product['screen_size'] ?? 0) > 0) $specs['Kích thước màn hình'] = (string) (float) $product['screen_size'] . ' inch';
        if (!empty($product['panel'])) $specs['Tấm nền'] = (string) $product['panel'];
        if (!empty($product['resolution'])) $specs['Độ phân giải'] = (string) $product['resolution'];
        if ((int) ($product['refresh_rate'] ?? 0) > 0) $specs['Tần số quét'] = (int) $product['refresh_rate'] . ' Hz';
        if (!empty($product['response_time'])) $specs['Thời gian phản hồi'] = (string) $product['response_time'];
    }
    if (!empty($product['usage_type'])) $specs['Nhu cầu sử dụng'] = (string) $product['usage_type'];
    return $specs;
}

function product_chips(array $product): array
{
    if (category_from_product($product) === 'monitor') {
        return array_values(array_filter([
            (float) ($product['screen_size'] ?? 0) > 0 ? (string) (float) $product['screen_size'] . ' inch' : '',
            (string) ($product['resolution'] ?? ''),
            (int) ($product['refresh_rate'] ?? 0) > 0 ? (int) $product['refresh_rate'] . 'Hz' : '',
        ], static fn (string $value): bool => $value !== ''));
    }
    return array_values(array_filter([
        (string) ($product['cpu'] ?? ''),
        (string) ($product['ram'] ?? ''),
        (string) ($product['storage'] ?? ''),
    ], static fn (string $value): bool => $value !== ''));
}

function category_destination(string $category): string
{
    $definitions = shop_categories();
    if (!isset($definitions[$category])) $category = 'monitor';
    $names = ['monitor' => ['Màn hình'], 'pc' => ['PC', 'Máy tính'], 'laptop' => ['Laptop']];
    $navigation = require dirname(__DIR__) . '/config/categories.php';
    foreach ($navigation as $key => $entry) {
        if (($entry['category'] ?? $key) !== $category && !in_array($entry['name'] ?? '', $names[$category], true)) continue;
        $destination = trim((string) ($entry['url'] ?? ''));
        if ($destination === '') continue;
        if (preg_match('~^https?://~i', $destination) || str_starts_with($destination, '/')) return $destination;
        return url($destination);
    }
    return $category === 'monitor' ? url('monitor.php') : url('index.php#' . $definitions[$category]['anchor']);
}

function product_image_url(array $product): string
{
    $image = (string) ($product['image'] ?? '');
    return preg_match('~^https?://~i', $image) ? $image : url($image);
}

function product_details_url(array $product): string
{
    return url('product.php?slug=' . rawurlencode((string) ($product['slug'] ?? '')));
}
