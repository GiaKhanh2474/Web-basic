<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('cellphones_shop_session');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'use_strict_mode' => true,
    ]);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $config = require dirname(__DIR__) . '/config/database.php';
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(mixed $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . 'đ';
}

function url(string $path = ''): string
{
    $root = str_replace('\\', '/', dirname(__DIR__));
    $documentRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($documentRoot !== '' && str_starts_with(strtolower($root . '/'), strtolower($documentRoot . '/'))) {
        $base = substr($root, strlen($documentRoot));
    } else {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/cellphones-shop/laptop.php');
        $base = preg_replace('~/(?:api/)?[^/]+\.php$~', '', $script) ?: '';
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
