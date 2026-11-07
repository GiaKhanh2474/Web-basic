<?php
/** Run: C:\xampp\php\php.exe tests\regressions.php http://localhost/cellphones-shop/ */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/catalog.php';

$base = rtrim($argv[1] ?? 'http://localhost/cellphones-shop/', '/') . '/';
$passed = 0;
$temporaryId = null;
$failure = null;

function regression_check(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $passed++;
    echo 'PASS: ' . $label . PHP_EOL;
}

function regression_url(string $path): string
{
    global $base;
    if (preg_match('~^https?://~i', $path)) return $path;
    if (str_starts_with($path, '/')) {
        $parts = parse_url($base);
        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $path;
    }
    return $base . $path;
}

/** @return array{status:int,body:string} */
function regression_request(string $path): array
{
    $context = stream_context_create(['http' => [
        'ignore_errors' => true, 'timeout' => 15, 'header' => 'Connection: close',
    ]]);
    $body = @file_get_contents(regression_url($path), false, $context);
    if ($body === false) throw new RuntimeException('Cannot load ' . $path . '. Start Apache and MySQL first.');
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('~^HTTP/\S+\s+(\d+)~', $header, $match)) $status = (int) $match[1];
    }
    return ['status' => $status, 'body' => $body];
}

function regression_dom(string $html): DOMXPath
{
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($document);
}

/** @return list<string> */
function regression_slugs(DOMXPath $xpath): array
{
    $slugs = [];
    foreach ($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " product-card ")]//a[contains(concat(" ", normalize-space(@class), " "), " product-image ")]') as $link) {
        parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        if (!is_string($query['slug'] ?? null)) throw new RuntimeException('Product card has no detail slug.');
        $slugs[] = $query['slug'];
    }
    return $slugs;
}

function regression_same_slugs(array $actual, array $rows): bool
{
    $expected = array_column($rows, 'slug');
    sort($actual);
    sort($expected);
    return $actual === $expected;
}

function regression_class_xpath(string $tag, string $class): string
{
    return '//' . $tag . '[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]';
}

try {
    $pdo = db();
    $source = $pdo->query("SELECT * FROM products WHERE category = 'monitor' ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    regression_check(is_array($source), 'Monitor fixture can be based on an existing catalog row');
    unset($source['id'], $source['created_at'], $source['updated_at']);
    $source['slug'] = 'qa-review-' . bin2hex(random_bytes(6));
    $source['name'] = 'QA monitor route ' . $source['slug'];
    $source['image'] = 'https://example.com/monitor-test.jpg?size=large&view=front';
    $columns = array_keys($source);
    foreach ($columns as $column) {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $column)) throw new RuntimeException('Unexpected column name.');
    }
    $insert = $pdo->prepare('INSERT INTO products (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')');
    $insert->execute(array_values($source));
    $temporaryId = (int) $pdo->lastInsertId();

    $monitors = $pdo->query("SELECT * FROM products WHERE category = 'monitor' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $catalog = regression_request('monitor.php');
    $catalogDom = regression_dom($catalog['body']);
    regression_check($catalog['status'] === 200 && regression_same_slugs(regression_slugs($catalogDom), $monitors), 'monitor.php displays all monitor products');
    $alias = regression_request('man-hinh.html');
    regression_check($alias['status'] === 200 && regression_same_slugs(regression_slugs(regression_dom($alias['body'])), $monitors), 'man-hinh.html resolves to the same monitor catalog');

    $searchAction = $catalogDom->evaluate('string(//form[@role="search"]/@action)');
    $filterAction = $catalogDom->evaluate('string(' . regression_class_xpath('form', 'filter-form') . '/@action)');
    regression_check(basename((string) parse_url($searchAction, PHP_URL_PATH)) === 'monitor.php'
        && basename((string) parse_url($filterAction, PHP_URL_PATH)) === 'monitor.php', 'Search and filter forms submit to monitor.php');
    $searched = regression_request(explode('#', $searchAction)[0] . '?q=' . rawurlencode($source['slug']));
    regression_check($searched['status'] === 200 && regression_slugs(regression_dom($searched['body'])) === [$source['slug']], 'Submitting the rendered search action finds the monitor fixture');

    $brandLink = $catalogDom->evaluate('string((' . regression_class_xpath('a', 'brand-pill') . '[not(contains(concat(" ", normalize-space(@class), " "), " brand-all "))])[1]/@href)');
    parse_str((string) parse_url($brandLink, PHP_URL_QUERY), $brandQuery);
    regression_check(isset($brandQuery['brand']), 'Catalog contains a selectable brand link');
    $brandPage = regression_request($brandLink);
    $expectedBrand = array_values(array_filter($monitors, static fn (array $row): bool => $row['brand'] === $brandQuery['brand']));
    regression_check($brandPage['status'] === 200 && regression_same_slugs(regression_slugs(regression_dom($brandPage['body'])), $expectedBrand), 'Rendered brand link opens the correct monitor results');

    $useLink = $catalogDom->evaluate('string((' . regression_class_xpath('a', 'need-chip') . ')[1]/@href)');
    parse_str((string) parse_url($useLink, PHP_URL_QUERY), $useQuery);
    regression_check(isset($useQuery['use']), 'Catalog contains a selectable usage link');
    $usePage = regression_request($useLink);
    $expectedUse = array_values(array_filter($monitors, static fn (array $row): bool => $row['usage_type'] === $useQuery['use']));
    regression_check($usePage['status'] === 200 && regression_same_slugs(regression_slugs(regression_dom($usePage['body'])), $expectedUse), 'Rendered usage link opens the correct monitor results');

    $sortLink = '';
    foreach ($catalogDom->query(regression_class_xpath('a', 'sort-button')) as $link) {
        parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        if (($query['sort'] ?? '') === 'price_asc') $sortLink = $link->getAttribute('href');
    }
    regression_check($sortLink !== '', 'Catalog contains an ascending price link');
    $sorted = regression_request($sortLink);
    $expectedSorted = $monitors;
    usort($expectedSorted, static fn (array $left, array $right): int => ((int) $left['price'] <=> (int) $right['price']) ?: ((int) $left['id'] <=> (int) $right['id']));
    regression_check($sorted['status'] === 200 && regression_slugs(regression_dom($sorted['body'])) === array_column($expectedSorted, 'slug'), 'Rendered sort link orders monitor products by price');

    $filtered = regression_request(explode('#', $filterAction)[0] . '?resolution=' . rawurlencode($source['resolution']));
    $expectedResolution = array_values(array_filter($monitors, static fn (array $row): bool => $row['resolution'] === $source['resolution']));
    regression_check($filtered['status'] === 200 && regression_same_slugs(regression_slugs(regression_dom($filtered['body'])), $expectedResolution), 'Submitting the rendered filter form applies monitor resolution');

    $detail = regression_request('product.php?slug=' . rawurlencode($source['slug']));
    $detailDom = regression_dom($detail['body']);
    $galleryImage = $detailDom->evaluate('string(' . regression_class_xpath('div', 'gallery-stage') . '/img/@src)');
    regression_check($detail['status'] === 200 && $galleryImage === $source['image'], 'Monitor gallery preserves HTTP(S) image URLs stored in MySQL');
    $matchingCardImage = '';
    foreach ($catalogDom->query(regression_class_xpath('a', 'product-image')) as $link) {
        parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        if (($query['slug'] ?? '') === $source['slug']) $matchingCardImage = $catalogDom->evaluate('string(.//img/@src)', $link);
    }
    regression_check($matchingCardImage === $source['image'], 'Product card and monitor detail use the same remote image');

    $projectPath = rtrim((string) parse_url($base, PHP_URL_PATH), '/') . '/';
    $origin = parse_url($base);
    $checkedPaths = [];
    foreach (['homepage' => regression_request('index.php'), 'catalog' => $catalog, 'detail' => $detail, 'cart' => regression_request('cart.php')] as $label => $page) {
        regression_check($page['status'] === 200, $label . ' loads during navigation review');
        foreach (regression_dom($page['body'])->query('//a[@href]/@href | //form[@action]/@action') as $attribute) {
            $target = parse_url(regression_url($attribute->nodeValue));
            if (!$target || ($target['host'] ?? '') !== $origin['host'] || ($target['port'] ?? null) !== ($origin['port'] ?? null)) continue;
            $path = rawurldecode($target['path'] ?? '');
            if (!str_starts_with($path, $projectPath) || !str_ends_with($path, '.php')) continue;
            $relative = substr($path, strlen($projectPath));
            if (!is_file(dirname(__DIR__) . '/' . $relative)) throw new RuntimeException('FAIL: ' . $label . ' links to missing file ' . $relative);
            $checkedPaths[$relative] = true;
        }
    }
    regression_check(isset($checkedPaths['monitor.php'], $checkedPaths['index.php'], $checkedPaths['product.php'], $checkedPaths['cart.php']), 'Rendered navigation points to existing PHP pages across the storefront');

    $update = $pdo->prepare("UPDATE products SET category = 'laptop', screen_size = 0, resolution = '', panel = '', refresh_rate = 0 WHERE id = ?");
    $update->execute([$temporaryId]);
    $laptop = regression_request('product.php?slug=' . rawurlencode($source['slug']));
    $laptopText = regression_dom($laptop['body'])->evaluate('string(//main)');
    regression_check($laptop['status'] === 200 && !preg_match('/(?<![\d.])0\s*inch\b/i', $laptopText), 'Laptop detail omits a zero-inch display when screen specifications are absent');
} catch (Throwable $exception) {
    $failure = $exception;
} finally {
    if ($temporaryId !== null) {
        try {
            $delete = db()->prepare('DELETE FROM products WHERE id = ?');
            $delete->execute([$temporaryId]);
            echo "Temporary regression product removed.\n";
        } catch (Throwable $exception) {
            $failure ??= $exception;
            fwrite(STDERR, 'Cleanup failed: ' . $exception->getMessage() . PHP_EOL);
        }
    }
}

if ($failure !== null) {
    fwrite(STDERR, $failure->getMessage() . PHP_EOL);
    exit(1);
}
echo PHP_EOL . $passed . " regression checks passed.\n";
