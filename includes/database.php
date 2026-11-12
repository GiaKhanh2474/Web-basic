<?php
declare(strict_types=1);

/** Shared by HTTP requests and CLI setup; does not start a session. */
function connect_database(bool $selectDatabase = true): PDO
{
    $config = require dirname(__DIR__) . '/config/database.php';
    if (getenv('APP_ENV') === 'production') {
        foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
            if (getenv($key) === false || getenv($key) === '') {
                throw new RuntimeException('Missing required environment variable: ' . $key);
            }
        }
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $config['database'])) {
        throw new RuntimeException('DB_DATABASE must contain only letters, digits and underscores.');
    }
    if (str_contains($config['host'], ';') || str_contains($config['host'], '://')) {
        throw new RuntimeException('DB_HOST must be a database hostname, without https://.');
    }
    if (!ctype_digit((string) $config['port']) || (int) $config['port'] < 1 || (int) $config['port'] > 65535) {
        throw new RuntimeException('Invalid DB_PORT.');
    }
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ];
    if ($config['ssl_ca'] !== '') {
        if (!is_readable($config['ssl_ca'])) {
            throw new RuntimeException('DB_SSL_CA must point to a readable CA certificate file.');
        }
        $options[PDO::MYSQL_ATTR_SSL_CA] = $config['ssl_ca'];
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
    if ($selectDatabase) $dsn .= ';dbname=' . $config['database'];
    return new PDO($dsn, $config['username'], $config['password'], $options);
}
