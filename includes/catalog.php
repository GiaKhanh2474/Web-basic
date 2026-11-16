<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function products(array $filters = []): array
{
    // A query string can contain array values (for example sort[]=popular).
    // Only scalar filter values are part of the catalog API.
    $filters = array_filter($filters, static fn (mixed $value): bool => is_scalar($value));
    $where = [];
    $params = [];
    $search = trim((string) ($filters['q'] ?? ''));
    if ($search !== '') {
        $where[] = '(name LIKE ? OR brand LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    foreach (['category' => 'category', 'brand' => 'brand', 'use' => 'usage_type', 'size' => 'screen_size', 'panel' => 'panel', 'resolution' => 'resolution'] as $key => $column) {
        if (isset($filters[$key]) && (string) $filters[$key] !== '') {
            $where[] = $column . ' = ?';
            $params[] = $filters[$key];
        }
    }
    // PC filters: match part of the specification text (for example 'Core i5', '16GB', '512GB').
    foreach (['cpu' => 'cpu', 'gpu' => 'gpu'] as $key => $column) {
        $value = trim((string) ($filters[$key] ?? ''));
        if ($value !== '') {
            $where[] = $column . ' LIKE ?';
            $params[] = '%' . addcslashes($value, '%_\\') . '%';
        }
    }
    foreach (['ram' => 'ram', 'storage' => 'storage'] as $key => $column) {
        $value = trim((string) ($filters[$key] ?? ''));
        if ($value !== '') {
            if (preg_match('/^(\d+(?:\.\d+)?)\s*(GB|TB)$/i', $value, $capacity)) {
                // Match a complete capacity anywhere: SSD 512GB, 512 GB SSD,
                // RAM 16GB. Do not match 16GB against 160GB or 512GB against 1512GB.
                $where[] = $column . ' REGEXP ?';
                $params[] = '(^|[^0-9.])' . preg_quote($capacity[1], '/')
                    . '[[:space:]]*' . strtoupper($capacity[2]) . '([^[:alnum:]]|$)';
            } else {
                $where[] = $column . ' LIKE ?';
                $params[] = '%' . addcslashes($value, '%_\\') . '%';
            }
        }
    }
    switch ($filters['price'] ?? '') {
        case 'under15': $where[] = 'price < 15000000'; break;
        case '15to25': $where[] = 'price >= 15000000 AND price <= 25000000'; break;
        case 'over25': $where[] = 'price > 25000000'; break;
        case 'under3': $where[] = 'price < 3000000'; break;
        case '3to5': $where[] = 'price >= 3000000 AND price <= 5000000'; break;
        case 'over5': $where[] = 'price > 5000000'; break;
    }
    $sort = [
        'popular' => 'featured DESC, review_count DESC, id ASC',
        'price_asc' => 'price ASC, id ASC',
        'price_desc' => 'price DESC, id ASC',
        'newest' => 'created_at DESC, id DESC',
    ][$filters['sort'] ?? 'popular'] ?? 'featured DESC, review_count DESC, id ASC';
    $sql = 'SELECT * FROM products' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $sort;
    $query = db()->prepare($sql);
    $query->execute($params);
    return $query->fetchAll();
}

function product_by_slug(string $slug): ?array
{
    $query = db()->prepare('SELECT * FROM products WHERE slug = ? LIMIT 1');
    $query->execute([$slug]);
    return $query->fetch() ?: null;
}

function product_by_id(int $id): ?array
{
    $query = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

function brands(?string $category = null): array
{
    if ($category !== null) {
        $query = db()->prepare('SELECT DISTINCT brand FROM products WHERE category = ? ORDER BY brand');
        $query->execute([$category]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }
    return db()->query('SELECT DISTINCT brand FROM products ORDER BY brand')->fetchAll(PDO::FETCH_COLUMN);
}
