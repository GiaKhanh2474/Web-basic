<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$pdo = db();
if (!str_starts_with((string) $pdo->query('SELECT DATABASE()')->fetchColumn(), 'web_basic_qa_')) {
    fwrite(STDERR, "Use an isolated database named web_basic_qa_* via DB_DATABASE.\n");
    exit(1);
}
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8099', '/');
$jar = tempnam(sys_get_temp_dir(), 'laptop-qa-');
$inserted = [];
$passed = 0;
$failure = null;
function laptop_check(bool $condition, string $label): void {
    global $passed;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $passed++;
    echo 'PASS: ' . $label . PHP_EOL;
}
function laptop_request(string $path, ?array $payload = null): array {
    global $base, $jar;
    $curl = curl_init($base . '/' . ltrim($path, '/'));
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_COOKIEFILE => $jar, CURLOPT_COOKIEJAR => $jar]);
    if ($payload !== null) curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode($payload)]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if ($body === false) throw new RuntimeException('Could not request ' . $path);
    return [$status, $body];
}
function laptop_dom(string $html): DOMXPath {
    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($doc);
}
function laptop_card_ids(string $html): array {
    $ids = [];
    foreach (laptop_dom($html)->query('//article[contains(@class,"product-card")]//button[@data-add-cart]') as $button) $ids[] = (int) $button->getAttribute('data-add-cart');
    return $ids;
}
try {
    [$status, $body] = laptop_request('laptop.php');
    $ids = laptop_card_ids($body);
    $expected = array_map('intval', $pdo->query("SELECT id FROM products WHERE category='laptop'")->fetchAll(PDO::FETCH_COLUMN));
    sort($ids); sort($expected);
    laptop_check($status === 200 && $expected && $ids === $expected, 'Laptop catalogue contains exactly the laptop rows in MySQL');
    $xpath = laptop_dom($body);
    laptop_check(str_ends_with($xpath->evaluate('string(//form[contains(@class,"search-form")]/@action)'), 'laptop.php'), 'Header search submits to laptop catalogue');
    laptop_check(str_ends_with($xpath->evaluate('string(//form[contains(@class,"filter-form")]/@action)'), 'laptop.php#catalog'), 'Filter form stays on laptop catalogue');
    foreach (['index.php', 'monitor.php', 'pc.php', 'cart.php'] as $page) {
        [$status, $html] = laptop_request($page);
        laptop_check($status === 200 && laptop_dom($html)->query('//nav[@id="category-navigation"]//a[contains(@href,"laptop.php")]')->length === 1, "$page links to the new Laptop category");
    }
    foreach (['monitor' => 'monitor.php', 'pc' => 'pc.php'] as $category => $page) {
        [$status, $html] = laptop_request($page);
        $actual = laptop_card_ids($html);
        $expected = array_map('intval', $pdo->query("SELECT id FROM products WHERE category='$category'")->fetchAll(PDO::FETCH_COLUMN));
        sort($actual); sort($expected);
        laptop_check($status === 200 && $actual === $expected, "$category catalogue still contains its own category only");
    }
    $source = $pdo->query("SELECT * FROM products WHERE category='laptop' ORDER BY id LIMIT 1")->fetch();
    unset($source['id'], $source['created_at'], $source['updated_at']);
    $marker = 'qa-laptop-' . bin2hex(random_bytes(5));
    $source = array_replace($source, ['slug' => $marker, 'name' => $marker . ' <script>alert(1)</script>', 'brand' => 'QA Laptop', 'cpu' => 'Intel Core i5', 'ram' => 'RAM 16 GB DDR5', 'storage' => 'SSD 512 GB', 'screen_size' => '15.6', 'price' => 12345678, 'stock' => 5]);
    $columns = array_keys($source);
    $insert = $pdo->prepare('INSERT INTO products (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')');
    $insert->execute(array_values($source));
    $laptopId = (int) $pdo->lastInsertId(); $inserted[] = $laptopId;
    $source['slug'] .= '-monitor'; $source['category'] = 'monitor';
    $insert->execute(array_values($source)); $inserted[] = (int) $pdo->lastInsertId();
    [$status, $html] = laptop_request('laptop.php?q=' . rawurlencode($marker));
    laptop_check($status === 200 && laptop_card_ids($html) === [$laptopId], 'Search finds new laptop and excludes matching monitor');
    laptop_check(!str_contains($html, '<script>alert(1)</script>') && str_contains($html, '&lt;script&gt;'), 'Laptop product names are HTML escaped');
    foreach (['brand' => 'QA Laptop', 'cpu' => 'Core i5', 'ram' => '16GB', 'storage' => '512GB', 'size' => '15.6', 'price' => 'under15'] as $key => $value) {
        [$status, $html] = laptop_request('laptop.php?' . http_build_query(['q' => $marker, $key => $value]));
        laptop_check($status === 200 && laptop_card_ids($html) === [$laptopId], "$key filter finds the laptop fixture");
    }
    [$status, $html] = laptop_request('laptop.php?' . http_build_query(['q' => $marker, 'ram' => '8GB']));
    laptop_check($status === 200 && laptop_card_ids($html) === [], 'Nonmatching RAM filter shows an empty catalogue');
    [$status, $html] = laptop_request('laptop.php?sort=price_asc');
    $expected = array_map('intval', $pdo->query("SELECT id FROM products WHERE category='laptop' ORDER BY price,id")->fetchAll(PDO::FETCH_COLUMN));
    laptop_check($status === 200 && laptop_card_ids($html) === $expected, 'Laptop prices sort ascending');
    [$status, $html] = laptop_request('laptop.php?q[]=x&sort[]=price_asc');
    laptop_check($status === 200, 'Array query parameters do not crash laptop catalogue');
    [$status, $html] = laptop_request('product.php?slug=' . rawurlencode($marker));
    $xpath = laptop_dom($html);
    laptop_check($status === 200 && str_contains($xpath->evaluate('string(//h1)'), $marker) && str_contains($html, 'Intel Core i5'), 'Laptop opens shared product detail with computer specifications');
    $token = $xpath->evaluate('string(//meta[@name="csrf-token"]/@content)');
    [$status, $body] = laptop_request('api/cart.php', ['csrf_token' => $token, 'action' => 'add', 'product_id' => $laptopId, 'quantity' => 2]);
    $data = json_decode($body, true);
    laptop_check($status === 200 && ($data['success'] ?? false) && ($data['total'] ?? 0) === 24691356 && str_contains($data['message'] ?? '', 'Đã thêm vào giỏ hàng ('), 'Laptop uses existing cart API, database price and Vietnamese notification');
    [$status, $html] = laptop_request('cart.php');
    laptop_check($status === 200 && str_contains($html, $marker) && !str_contains($html, '<script>alert(1)</script>'), 'Cart displays and escapes the added laptop');
    [$status, $body] = laptop_request('api/cart.php', ['csrf_token' => $token, 'action' => 'remove', 'product_id' => $laptopId]);
    laptop_check($status === 200 && (json_decode($body, true)['count'] ?? -1) === 0, 'Laptop can be removed from the shared cart');
} catch (Throwable $error) {
    $failure = $error;
} finally {
    foreach ($inserted as $id) $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    if (is_file($jar)) unlink($jar);
}
if ($failure) { fwrite(STDERR, $failure->getMessage() . PHP_EOL); exit(1); }
echo "$passed laptop checks passed. Temporary products removed.\n";
