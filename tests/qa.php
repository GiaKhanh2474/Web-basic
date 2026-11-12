<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/catalog.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8094/', '/');
$apacheBase = isset($argv[2]) ? rtrim($argv[2], '/') : null;
$passed = 0;
function qa_check(bool $ok, string $label): void {
    global $passed;
    if (!$ok) throw new RuntimeException('FAIL: ' . $label);
    echo 'PASS: ' . $label . PHP_EOL;
    $passed++;
}
function qa_status(string $url): int {
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return $response === false ? 0 : $status;
}
$pdo = db();
if (!str_starts_with((string) $pdo->query('SELECT DATABASE()')->fetchColumn(), 'web_basic_qa_')) {
    fwrite(STDERR, "Use an isolated database named web_basic_qa_* via DB_DATABASE.\n");
    exit(1);
}
try {
    foreach (array_filter(['PHP server' => $base, 'Apache' => $apacheBase]) as $server => $url) {
        foreach (['man-hinh.html', 'gio-hang.html', 'pc.php'] as $path) {
            qa_check(qa_status($url . '/' . $path) === 200, "$server route $path");
        }
        foreach (['database/schema.sql', '.git/config', 'config/', 'scripts/setup.php', '.htaccess'] as $path) {
            qa_check(qa_status($url . '/' . $path) === 403, "$server protects $path");
        }
    }
    $pdo->beginTransaction();
    $source = $pdo->query("SELECT * FROM products WHERE category='pc' ORDER BY id LIMIT 1")->fetch();
    if (!$source) throw new RuntimeException('Seed the isolated database first.');
    unset($source['id'], $source['created_at'], $source['updated_at']);
    $source['slug'] = 'pc-qa-' . bin2hex(random_bytes(5)) . '-demo';
    $source['name'] = 'QA edited PC';
    $source['price'] = 1234567;
    $source['stock'] = 7;
    $columns = array_keys($source);
    $insert = $pdo->prepare('INSERT INTO products (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')');
    $insert->execute(array_values($source));
    $id = (int) $pdo->lastInsertId();
    $update = $pdo->prepare('UPDATE products SET storage=?, ram=? WHERE id=?');
    foreach ([['SSD 512GB', 'RAM 16GB', true], ['512 GB SSD', '16 GB DDR5', true], ['1512GB SSD', '160GB', false], ['512GB SSD', '16GB', true]] as [$storage, $ram, $expected]) {
        $update->execute([$storage, $ram, $id]);
        foreach (['storage' => '512GB', 'ram' => '16GB'] as $key => $value) {
            $ids = array_map('intval', array_column(products(['category' => 'pc', $key => $value]), 'id'));
            qa_check(in_array($id, $ids, true) === $expected, "$key matches complete capacity in " . ($key === 'ram' ? $ram : $storage));
        }
    }
    $before = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
    $sql = file_get_contents(dirname(__DIR__) . '/database/pc-products.sql');
    // Select the isolated database rather than the manual phpMyAdmin target.
    $sql = preg_replace('/^USE\s+cellphones_shop;\s*$/mi', '', $sql);
    for ($iteration = 1; $iteration <= 2; $iteration++) {
        $pdo->exec($sql);
        qa_check($before === $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll(), "PC import $iteration preserves edited products and avoids duplicates");
    }
    $pdo->rollBack();
    echo "$passed QA checks passed. All fixture changes rolled back.\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
