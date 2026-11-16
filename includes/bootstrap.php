<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('cellphones_shop_session');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => getenv('APP_ENV') === 'production' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'use_strict_mode' => true,
    ]);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = connect_database();
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
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/cellphones-shop/monitor.php');
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
