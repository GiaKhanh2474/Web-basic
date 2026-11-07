<?php
/** Run: C:\xampp\php\php.exe tests\homepage.php http://localhost/cellphones-shop/ */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/home-catalog.php';
require_once dirname(__DIR__) . '/scripts/setup-homepage.php';

$base = rtrim($argv[1] ?? 'http://localhost/cellphones-shop/', '/') . '/';
$passed = 0;
$cookies = [];
$cleanupIds = [];
$csrf = null;
$failure = null;

function home_check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $passed++;
    echo 'PASS: ' . $label . PHP_EOL;
}

/** @return array{status:int,body:string,json:mixed} */
function home_request(string $path, array &$jar, ?array $payload = null): array
{
    global $base;
    $headers = ['Accept: application/json, text/html', 'Connection: close'];
    if ($jar) {
        $pairs = [];
        foreach ($jar as $name => $value) $pairs[] = $name . '=' . $value;
        $headers[] = 'Cookie: ' . implode('; ', $pairs);
    }
    $options = ['method' => $payload === null ? 'GET' : 'POST', 'header' => $headers, 'ignore_errors' => true, 'timeout' => 15];
    if ($payload !== null) {
        $options['header'][] = 'Content-Type: application/json';
        $options['content'] = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
    $body = @file_get_contents($base . ltrim($path, '/'), false, stream_context_create(['http' => $options]));
    if ($body === false) throw new RuntimeException('Cannot connect to ' . $base . '. Start Apache and MySQL first.');
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('~^HTTP/\S+\s+(\d+)~', $header, $match)) $status = (int) $match[1];
        if (preg_match('~^Set-Cookie:\s*([^=;\s]+)=([^;]*)~i', $header, $match)) $jar[$match[1]] = $match[2];
    }
    return ['status' => $status, 'body' => $body, 'json' => json_decode($body, true)];
}

function home_dom(string $html): DOMXPath
{
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($document);
}

/** @return list<string> */
function home_card_slugs(DOMXPath $xpath, ?DOMNode $context = null): array
{
    $slugs = [];
    $cards = $xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " product-card ")]', $context);
    foreach ($cards as $card) {
        $link = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " product-image ")]', $card)->item(0);
        if (!$link instanceof DOMElement) throw new RuntimeException('A product card is missing its detail link.');
        parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        if (!is_string($query['slug'] ?? null)) throw new RuntimeException('A detail link is missing its product slug.');
        $slugs[] = $query['slug'];
    }
    sort($slugs);
    return $slugs;
}

function home_add(array &$jar, string $token, string $action, int $id): array
{
    return home_request('api/cart.php', $jar, [
        'action' => $action, 'product_id' => $id, 'quantity' => 1,
        'csrf_token' => $token, 'price' => 1,
    ]);
}

try {
    $pdo = db();
    $databaseRows = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $groups = ['monitor' => [], 'pc' => [], 'laptop' => []];
    foreach ($databaseRows as $row) {
        $category = (string) ($row['category'] ?? 'monitor');
        if (isset($groups[$category])) $groups[$category][] = $row;
    }
    home_check(count(array_filter($groups, static fn (array $rows): bool => count($rows) > 0)) === 3, 'MySQL contains products for all three homepage categories');

    $home = home_request('index.php', $cookies);
    home_check($home['status'] === 200, 'Homepage responds successfully');
    $xpath = home_dom($home['body']);
    $sections = $xpath->query('//*[@data-product-section]');
    $sectionIds = [];
    foreach ($sections as $section) $sectionIds[] = $section->getAttribute('id');
    home_check($sectionIds === ['man-hinh', 'may-tinh', 'laptop'], 'Homepage sections appear in monitor, PC, laptop order');
    foreach (['monitor' => 'man-hinh', 'pc' => 'may-tinh', 'laptop' => 'laptop'] as $category => $sectionId) {
        $section = $xpath->query('//*[@id="' . $sectionId . '"]')->item(0);
        $expected = array_column($groups[$category], 'slug');
        sort($expected);
        home_check($section !== null && home_card_slugs($xpath, $section) === $expected, $category . ' row contains exactly its current MySQL products');
    }
    $csrf = $xpath->evaluate('string(//meta[@name="csrf-token"]/@content)');
    home_check(is_string($csrf) && $csrf !== '', 'Homepage exposes a CSRF token for cart actions');

    $catalog = home_request('monitor.php', $cookies);
    $catalogSlugs = home_card_slugs(home_dom($catalog['body']));
    $monitorSlugs = array_column($groups['monitor'], 'slug');
    sort($monitorSlugs);
    home_check($catalog['status'] === 200 && $catalogSlugs === $monitorSlugs, 'Existing monitor.php catalog still displays monitors only');

    $representatives = [];
    foreach ($groups as $category => $rows) {
        foreach ($rows as $row) {
            if ((int) $row['stock'] > 0) {
                $representatives[$category] = $row;
                break;
            }
        }
    }
    home_check(count($representatives) === 3, 'Each category has an in-stock product for the cart check');
    foreach (['pc', 'laptop'] as $category) {
        $product = $representatives[$category];
        $detail = home_request('product.php?slug=' . rawurlencode($product['slug']), $cookies);
        $detailXpath = home_dom($detail['body']);
        $text = $detailXpath->evaluate('string(//main)');
        home_check($detail['status'] === 200 && str_contains($text, $product['name']), $category . ' card opens its own database-backed detail page');
        $hasSpecifications = true;
        foreach (['cpu', 'ram', 'storage', 'gpu'] as $field) {
            if (trim((string) $product[$field]) !== '' && !str_contains($text, (string) $product[$field])) $hasSpecifications = false;
        }
        home_check($hasSpecifications && !preg_match('/(?<![\d.])0\s*inch\b/i', $text), $category . ' detail shows computer specifications without a zero-inch monitor');
    }

    $laptop = $representatives['laptop'];
    $search = home_request('index.php?q=' . rawurlencode($laptop['name']), $cookies);
    $searchSlugs = home_card_slugs(home_dom($search['body']));
    home_check($search['status'] === 200 && in_array($laptop['slug'], $searchSlugs, true), 'Homepage search finds products outside the monitor category');
    $probe = '<script>alert("homepage-check")</script>';
    $escapedSearch = home_request('index.php?q=' . rawurlencode($probe), $cookies);
    $escapedXpath = home_dom($escapedSearch['body']);
    home_check($escapedSearch['status'] === 200 && !str_contains($escapedSearch['body'], $probe)
        && str_contains($escapedXpath->evaluate('string(//h1)'), $probe), 'Search query is rendered as text rather than executable HTML');

    $navigation = require dirname(__DIR__) . '/config/categories.php';
    foreach (['pc' => 'may-tinh', 'laptop' => 'laptop'] as $category => $anchor) {
        $configured = '';
        foreach ($navigation as $entry) {
            if (($entry['category'] ?? null) === $category) $configured = trim((string) ($entry['url'] ?? ''));
        }
        if ($configured === '') home_check(str_ends_with(category_destination($category), '/index.php#' . $anchor), $category . ' links fall back to its homepage section until a URL is configured');
    }

    $expectedTotal = 0;
    $expectedCount = 0;
    foreach ($representatives as $category => $product) {
        $id = (int) $product['id'];
        $cleanupIds[] = $id;
        $added = home_add($cookies, $csrf, 'add', $id);
        $expectedCount++;
        $expectedTotal += (int) $product['price'];
        home_check($added['status'] === 200 && ($added['json']['success'] ?? false)
            && ($added['json']['message'] ?? '') === 'Đã thêm vào giỏ hàng (' . $product['name'] . ')', $category . ' can be added with the requested notification');
        home_check((int) ($added['json']['count'] ?? -1) === $expectedCount
            && (int) ($added['json']['total'] ?? -1) === $expectedTotal, 'Mixed-category cart uses MySQL prices after adding ' . $category);
    }
    $cart = home_request('cart.php', $cookies);
    $cartText = home_dom($cart['body'])->evaluate('string(//main)');
    $allInCart = $cart['status'] === 200;
    foreach ($representatives as $product) $allInCart = $allInCart && str_contains($cartText, $product['name']);
    home_check($allInCart, 'Cart page displays monitor, PC and laptop together');
    foreach ($cleanupIds as $id) {
        $removed = home_add($cookies, $csrf, 'remove', $id);
        home_check($removed['status'] === 200 && ($removed['json']['success'] ?? false), 'Mixed-category cart item can be removed');
    }
    $cleanupIds = [];
    home_check((int) ($removed['json']['count'] ?? -1) === 0 && (int) ($removed['json']['total'] ?? -1) === 0, 'Test cart is empty after removing all items');

    // Setup intentionally restores missing demo products; do not run it on a customized
    // catalog as part of ordinary HTTP checks. Opt in only on a disposable test database.
    if (in_array('--check-migration', $argv, true)) {
        $before = hash('sha256', json_encode($pdo->query('SELECT * FROM products ORDER BY id')->fetchAll(PDO::FETCH_ASSOC), JSON_THROW_ON_ERROR));
        setup_homepage($pdo);
        $after = hash('sha256', json_encode($pdo->query('SELECT * FROM products ORDER BY id')->fetchAll(PDO::FETCH_ASSOC), JSON_THROW_ON_ERROR));
        home_check(hash_equals($before, $after), 'Re-running homepage setup preserves every existing product field and avoids duplicate products');
    }
} catch (Throwable $exception) {
    $failure = $exception;
} finally {
    if ($csrf !== null) {
        foreach ($cleanupIds as $id) {
            try { home_add($cookies, $csrf, 'remove', $id); }
            catch (Throwable $exception) { fwrite(STDERR, 'Cart cleanup: ' . $exception->getMessage() . PHP_EOL); }
        }
    }
}

if ($failure !== null) {
    fwrite(STDERR, $failure->getMessage() . PHP_EOL);
    exit(1);
}
echo PHP_EOL . $passed . " homepage checks passed.\n";
