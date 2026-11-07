<?php
// Router for PHP's local development server. Apache uses .htaccess instead.
$path = str_replace('\\', '/', rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'));
if (preg_match('~(?:^|/)(?:config|database|includes|scripts|tests|storage|\.git)(?:/|$)|(?:README\.md|\.gitignore)$~i', $path)) {
    http_response_code(403); echo 'Forbidden'; return true;
}
if ($path === '/man-hinh.html') { require __DIR__.'/laptop.php'; return true; }
if ($path === '/gio-hang.html') { require __DIR__.'/cart.php'; return true; }
if (preg_match('~^/(man-hinh-[a-z0-9-]+)\.html$~', $path, $match)) { $_GET['slug'] = $match[1]; require __DIR__.'/product.php'; return true; }
return false;
