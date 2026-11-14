<?php
declare(strict_types=1);

// SQLite by default (zero setup). Set DB_DRIVER=mysql plus DB_HOST/DB_NAME/DB_USER/DB_PASS for MySQL.
function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ((getenv('DB_DRIVER') ?: 'sqlite') === 'mysql') {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_NAME') ?: 'inventory_demo');
        $pdo = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', $opts);
    } else {
        $pdo = new PDO('sqlite:' . __DIR__ . '/data/inventory.sqlite', null, null, $opts);
        $pdo->exec('CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sku TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            price REAL NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
    }
    return $pdo;
}
