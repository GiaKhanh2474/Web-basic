<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/includes/catalog.php';
$base = rtrim($argv[1] ?? 'http://localhost/cellphones-shop/', '/');
$failures = [];
function check_url(string $url, int $expected): bool {
    $handle = curl_init($url);
    curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>10]);
    $body = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    return $body !== false && $status === $expected;
}
foreach (products() as $product) {
    $path = dirname(__DIR__).'/'.$product['image'];
    $image = is_file($path) ? @getimagesize($path) : false;
    $imageUrl = $base.'/'.implode('/', array_map('rawurlencode', explode('/', $product['image'])));
    if (!$image || !check_url($imageUrl, 200)) { $failures[] = 'Missing image: '.$product['image']; continue; }
    if (!check_url($base.'/product.php?slug='.rawurlencode($product['slug']), 200)) { $failures[] = 'Broken product: '.$product['slug']; continue; }
    echo 'PASS: '.$product['brand'].' product page and image ('.$image[0].'x'.$image[1].")\n";
}
foreach (['man-hinh.html'=>200,'man-hinh-xiaomi-a27qi-2026-27-inch.html'=>200,'gio-hang.html'=>200,'database/schema.sql'=>403,'config/database.php'=>403,'scripts/setup.php'=>403,'tests/smoke.php'=>403] as $path=>$expected) {
    if (!check_url($base.'/'.$path, $expected)) { $failures[] = 'Unexpected route status: '.$path; } else { echo 'PASS: '.$path.' HTTP '.$expected."\n"; }
}
foreach (['under3'=>fn($p)=>$p['price'] < 3000000,'3to5'=>fn($p)=>$p['price'] >= 3000000 && $p['price'] <= 5000000,'over5'=>fn($p)=>$p['price'] > 5000000] as $filter=>$matches) {
    foreach(products(['price'=>$filter]) as $product) { if (!$matches($product)) { $failures[]='Incorrect price filter: '.$filter; } }
}
$prices=array_column(products(['sort'=>'price_asc']), 'price'); $expected=$prices; sort($expected, SORT_NUMERIC);
if ($prices !== $expected) $failures[]='Incorrect ascending price sort';
if ($failures) { fwrite(STDERR, implode("\n", $failures)."\n"); exit(1); }
echo "All product images, detail pages, routes and filter checks passed.\n";
