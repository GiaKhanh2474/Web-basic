<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$config = require dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/database.php';
if (!preg_match('/^[A-Za-z0-9_]+$/', $config['database'])) {
    fwrite(STDERR, "Invalid database name.\n");
    exit(1);
}
try {
    // Hosted providers can restrict CREATE DATABASE; use their existing database.
    $existingDatabase = in_array('--existing-database', $argv, true);
    $pdo = connect_database($existingDatabase);
    if (!$existingDatabase) {
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $config['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . $config['database'] . '`');
    }
    $sql = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
    $sql = preg_replace('/^(CREATE DATABASE IF NOT EXISTS|USE) .*?;\s*/m', '', $sql);
    $pdo->exec($sql);
    require_once __DIR__ . '/setup-homepage.php';
    setup_homepage($pdo);
    echo "Database ready: " . $config['database'] . "\nProducts: " . $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Setup failed. Check database availability, DB_* environment variables and the TLS CA file.\n" . $exception->getMessage() . "\n");
    exit(1);
}
