<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$config = require dirname(__DIR__) . '/config/database.php';
if (!preg_match('/^[A-Za-z0-9_]+$/', $config['database'])) {
    fwrite(STDERR, "Invalid database name.\n");
    exit(1);
}
try {
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $config['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . $config['database'] . '`');
    $sql = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
    $sql = preg_replace('/^(CREATE DATABASE IF NOT EXISTS|USE) .*?;\s*/m', '', $sql);
    $pdo->exec($sql);
    require_once __DIR__ . '/setup-homepage.php';
    setup_homepage($pdo);
    echo "Database ready: " . $config['database'] . "\nProducts: " . $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Setup failed. Start MySQL in XAMPP and check config/database.php.\n" . $exception->getMessage() . "\n");
    exit(1);
}
