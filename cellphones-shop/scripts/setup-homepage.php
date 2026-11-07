<?php
declare(strict_types=1);

// All schema changes are explicit CLI actions, never part of a page request.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function setup_homepage(PDO $pdo): array
{
    $existingColumns = $pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_COLUMN);
    $definitions = [
        'category' => "VARCHAR(20) NOT NULL DEFAULT 'monitor'",
        'cpu' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'ram' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'storage' => "VARCHAR(100) NOT NULL DEFAULT ''",
        'gpu' => "VARCHAR(100) NOT NULL DEFAULT ''",
    ];
    foreach ($definitions as $column => $definition) {
        if (!in_array($column, $existingColumns, true)) {
            $pdo->exec('ALTER TABLE products ADD COLUMN `' . $column . '` ' . $definition);
        }
    }
    $index = $pdo->query("SHOW INDEX FROM products WHERE Key_name = 'products_category_index'")->fetch();
    if (!$index) {
        $pdo->exec('CREATE INDEX products_category_index ON products (category)');
    }

    $sql = file_get_contents(dirname(__DIR__) . '/database/homepage.sql');
    if ($sql === false) {
        throw new RuntimeException('Cannot read database/homepage.sql.');
    }
    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
    return $pdo->query('SELECT category, COUNT(*) AS total FROM products GROUP BY category ORDER BY category')->fetchAll(PDO::FETCH_ASSOC);
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require_once dirname(__DIR__) . '/includes/bootstrap.php';
    try {
        $counts = setup_homepage(db());
        echo "Homepage database ready. Existing products preserved.\n";
        foreach ($counts as $count) {
            echo $count['category'] . ': ' . $count['total'] . " products\n";
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, "Homepage setup failed. Start MySQL and initialize the base database first.\n" . $exception->getMessage() . "\n");
        exit(1);
    }
}
