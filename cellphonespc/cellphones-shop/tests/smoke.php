<?php
/** Run: C:\xampp\php\php.exe tests\smoke.php http://localhost/cellphones-shop/ */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/catalog.php';

$base = rtrim($argv[1] ?? 'http://localhost/cellphones-shop/', '/') . '/';
$passed = 0;
$temporaryId = null;
$cookies = [];
$otherCookies = [];

function check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    $passed++;
    echo 'PASS: ' . $label . PHP_EOL;
}

/** @return array{status:int,body:string,json:mixed} */
function request(string $path, array &$jar, string $method = 'GET', ?array $payload = null): array
{
    global $base;
    $headers = ['Accept: application/json, text/html', 'Connection: close'];
    if ($jar) {
        $pairs = [];
        foreach ($jar as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        $headers[] = 'Cookie: ' . implode('; ', $pairs);
    }
    $options = ['method' => $method, 'header' => $headers, 'ignore_errors' => true, 'timeout' => 15];
    if ($payload !== null) {
        $options['header'][] = 'Content-Type: application/json';
        $options['content'] = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
    $body = @file_get_contents($base . ltrim($path, '/'), false, stream_context_create(['http' => $options]));
    if ($body === false) {
        throw new RuntimeException('Cannot connect to ' . $base . '. Start Apache and MySQL in XAMPP first.');
    }
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('~^HTTP/\S+\s+(\d+)~', $header, $matches)) {
            $status = (int)$matches[1];
        }
        if (preg_match('~^Set-Cookie:\s*([^=;\s]+)=([^;]*)~i', $header, $matches)) {
            $jar[$matches[1]] = $matches[2];
        }
    }
    return ['status' => $status, 'body' => $body, 'json' => json_decode($body, true)];
}

function token_from(string $html): string
{
    if (preg_match('~<meta\b[^>]*name=["\x27]csrf-token["\x27][^>]*content=["\x27]([^"\x27]+)~i', $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    if (preg_match('~<meta\b[^>]*content=["\x27]([^"\x27]+)["\x27][^>]*name=["\x27]csrf-token["\x27]~i', $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    throw new RuntimeException('CSRF token is missing from page metadata.');
}

function mutate(array &$jar, string $token, string $action, int $productId, mixed $quantity = 1, array $extra = []): array
{
    return request('api/cart.php', $jar, 'POST', array_merge([
        'action' => $action,
        'product_id' => $productId,
        'quantity' => $quantity,
        'csrf_token' => $token,
    ], $extra));
}

try {
    $pdo = db();
    $source = $pdo->query('SELECT * FROM products ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    check(is_array($source), 'MySQL catalog contains seed products');

    $suffix = bin2hex(random_bytes(5));
    $testName = 'QA ' . $suffix . ' <script>alert(1)</script>';
    unset($source['id'], $source['created_at'], $source['updated_at']);
    $source['slug'] = 'qa-smoke-' . $suffix;
    $source['name'] = $testName;
    $source['price'] = 1234567;
    $source['stock'] = 3;
    if (array_key_exists('is_active', $source)) {
        $source['is_active'] = 1;
    }
    $columns = array_keys($source);
    foreach ($columns as $column) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $column)) {
            throw new RuntimeException('Unexpected database column name.');
        }
    }
    $sql = 'INSERT INTO products (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
    $pdo->prepare($sql)->execute(array_values($source));
    $temporaryId = (int)$pdo->lastInsertId();

    $home = request('laptop.php', $cookies);
    check($home['status'] === 200, 'Catalog responds successfully');
    $token = token_from($home['body']);
    check(strlen($token) >= 32, 'Page provides a session CSRF token');

    $search = request('laptop.php?q=' . rawurlencode('QA ' . $suffix), $cookies);
    check($search['status'] === 200 && str_contains($search['body'], $source['slug']), 'A product inserted into MySQL appears in catalog search');
    check(!str_contains($search['body'], $testName), 'Database product names cannot inject raw HTML into catalog');

    $detail = request('product.php?slug=' . rawurlencode($source['slug']), $cookies);
    check($detail['status'] === 200 && str_contains($detail['body'], $suffix), 'New MySQL product has a working detail page');
    check(!str_contains($detail['body'], $testName), 'Database product names cannot inject raw HTML into detail page');

    $xssQuery = '<script>alert("qa")</script>';
    $escapedSearch = request('laptop.php?q=' . rawurlencode($xssQuery), $cookies);
    check($escapedSearch['status'] === 200 && !str_contains($escapedSearch['body'], $xssQuery), 'Search input is escaped');
    $missing = request('product.php?slug=qa-missing-' . $suffix, $cookies);
    check($missing['status'] === 404, 'Unknown product returns HTTP 404');

    $readApi = request('api/cart.php', $cookies);
    check($readApi['status'] === 405, 'Cart mutations reject GET requests');
    $badToken = mutate($cookies, 'invalid-csrf-token', 'add', $temporaryId);
    check($badToken['status'] === 403, 'Cart rejects an invalid CSRF token');

    $added = mutate($cookies, $token, 'add', $temporaryId);
    check($added['status'] === 200 && ($added['json']['success'] ?? false) && (int)($added['json']['count'] ?? -1) === 1, 'Add creates one cart item');
    check(($added['json']['message'] ?? '') === 'Đã thêm vào giỏ hàng (' . $testName . ')', 'Add returns the requested Vietnamese notification with product name');
    check((int)($added['json']['total'] ?? -1) === 1234567, 'Cart total uses MySQL price');

    $duplicate = mutate($cookies, $token, 'add', $temporaryId);
    check($duplicate['status'] === 200 && (int)($duplicate['json']['count'] ?? -1) === 2, 'Adding the same product increments quantity');
    $updated = mutate($cookies, $token, 'update', $temporaryId, 3);
    check($updated['status'] === 200 && (int)($updated['json']['count'] ?? -1) === 3, 'Quantity can be updated to available stock');

    $overstock = mutate($cookies, $token, 'add', $temporaryId);
    check($overstock['status'] >= 400 || (int)($overstock['json']['count'] ?? PHP_INT_MAX) <= 3, 'Cart cannot exceed available stock');
    $negative = mutate($cookies, $token, 'add', $temporaryId, -2);
    check($negative['status'] >= 400 && !($negative['json']['success'] ?? false), 'Negative quantity is rejected');
    $zero = mutate($cookies, $token, 'add', $temporaryId, 0);
    check($zero['status'] >= 400 && !($zero['json']['success'] ?? false), 'Zero add quantity is rejected');
    $fraction = mutate($cookies, $token, 'add', $temporaryId, 1.5);
    check($fraction['status'] >= 400 && !($fraction['json']['success'] ?? false), 'Fractional quantity is rejected');
    $invalidId = mutate($cookies, $token, 'add', 2147483647);
    check($invalidId['status'] >= 400 && !($invalidId['json']['success'] ?? false), 'Unknown product cannot be added');
    $invalidAction = mutate($cookies, $token, 'checkout', $temporaryId);
    check($invalidAction['status'] >= 400 && !($invalidAction['json']['success'] ?? false), 'Unknown cart action is rejected');

    $tamperedPrice = mutate($cookies, $token, 'update', $temporaryId, 2, ['price' => 1, 'total' => 2]);
    check($tamperedPrice['status'] === 200 && (int)($tamperedPrice['json']['total'] ?? -1) === 2469134, 'Client-supplied price and total cannot override MySQL pricing');

    $otherHome = request('laptop.php', $otherCookies);
    $otherToken = token_from($otherHome['body']);
    $isolated = mutate($otherCookies, $otherToken, 'add', $temporaryId);
    check($isolated['status'] === 200 && (int)($isolated['json']['count'] ?? -1) === 1, 'Different sessions have independent carts');
    $cart = request('cart.php', $cookies);
    check($cart['status'] === 200 && str_contains($cart['body'], $suffix), 'Cart persists across page navigation');
    check(!str_contains($cart['body'], $testName), 'Database product names cannot inject raw HTML into cart');

    $badRemove = mutate($cookies, 'invalid-csrf-token', 'remove', $temporaryId);
    check($badRemove['status'] === 403, 'Removing an item also requires a valid CSRF token');
    $removed = mutate($cookies, $token, 'remove', $temporaryId);
    check($removed['status'] === 200 && (int)($removed['json']['count'] ?? -1) === 0 && (int)($removed['json']['total'] ?? -1) === 0, 'Remove empties the cart and resets totals');
    mutate($otherCookies, $otherToken, 'remove', $temporaryId);
    echo PHP_EOL . $passed . ' checks passed.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    $failed = true;
} finally {
    if ($temporaryId !== null && isset($pdo)) {
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$temporaryId]);
        echo 'Temporary QA product removed.' . PHP_EOL;
    }
}

exit(isset($failed) ? 1 : 0);
