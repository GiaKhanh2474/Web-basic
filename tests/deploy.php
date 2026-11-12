<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();

$originalEnv = getenv('APP_ENV');
putenv('APP_ENV=production');
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$secureCookie = session_get_cookie_params()['secure'];
session_write_close();
putenv($originalEnv === false ? 'APP_ENV' : 'APP_ENV=' . $originalEnv);
$passed = 0;
function deploy_check(bool $ok, string $label): void {
    global $passed;
    if (!$ok) throw new RuntimeException('FAIL: ' . $label);
    $passed++;
    echo 'PASS: ' . $label . PHP_EOL;
}
function deploy_request(string $url): array {
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 15]]);
    $body = file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    preg_match('~HTTP/\S+ (\d+)~', $headers[0] ?? '', $match);
    return [(int) ($match[1] ?? 0), $body, $headers];
}
function deploy_rejects(array $changes, string $expected): bool {
    $original = [];
    foreach ($changes as $key => $value) {
        $original[$key] = getenv($key);
        putenv($value === null ? $key : $key . '=' . $value);
    }
    try {
        connect_database();
        return false;
    } catch (RuntimeException $error) {
        return str_contains($error->getMessage(), $expected);
    } finally {
        foreach ($original as $key => $value) putenv($value === false ? $key : $key . '=' . $value);
    }
}
try {
    deploy_check($secureCookie, 'Production sessions require HTTPS behind Render proxy');
    deploy_check(deploy_rejects(['APP_ENV' => 'production', 'DB_PASSWORD' => null], 'Missing required'), 'Production rejects missing credentials');
    deploy_check(deploy_rejects(['DB_HOST' => 'https://example.com'], 'hostname'), 'Website URL is rejected as database host');
    deploy_check(deploy_rejects(['DB_PORT' => '0'], 'DB_PORT'), 'Invalid database port is rejected');
    deploy_check(deploy_rejects(['DB_DATABASE' => 'name;other=value'], 'DB_DATABASE'), 'Database name cannot inject DSN options');
    deploy_check(deploy_rejects(['DB_SSL_CA' => '/missing/test-ca.pem'], 'CA certificate'), 'Missing TLS CA fails closed');
    // The public failure response must not reveal configuration or exception details.
    $oldPort = getenv('DB_PORT');
    putenv('DB_PORT=0');
    ob_start();
    require dirname(__DIR__) . '/health.php';
    $body = ob_get_clean();
    putenv($oldPort === false ? 'DB_PORT' : 'DB_PORT=' . $oldPort);
    deploy_check(http_response_code() === 503 && $body === '{"status":"unavailable"}', 'Unavailable database returns a generic 503');

    $base = rtrim($argv[1] ?? 'http://127.0.0.1:8095', '/');
    [$status, $body, $headers] = deploy_request($base . '/health.php');
    deploy_check($status === 200 && json_decode($body, true) === ['status' => 'ok'], 'HTTP health check verifies database and products table');
    deploy_check(!preg_grep('/^Set-Cookie:/i', $headers), 'Health checks do not create sessions');
    foreach (['render.yaml', 'Dockerfile', '.env.example', 'deploy/start.sh', 'database/schema.sql'] as $path) {
        [$status] = deploy_request($base . '/' . $path);
        deploy_check(in_array($status, [403, 404], true), 'Deployment/private file is inaccessible: ' . $path);
    }
    echo "$passed deployment checks passed.\n";
} catch (Throwable $error) {
    ob_end_flush();
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
ob_end_flush();
