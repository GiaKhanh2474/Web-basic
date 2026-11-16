<?php
declare(strict_types=1);

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306',
    'database' => getenv('DB_DATABASE') ?: 'cellphones_shop',
    'username' => getenv('DB_USERNAME') !== false ? getenv('DB_USERNAME') : 'root',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'ssl_ca' => getenv('DB_SSL_CA') ?: (getenv('APP_ENV') === 'production' ? '/etc/ssl/certs/ca-certificates.crt' : ''),
];
