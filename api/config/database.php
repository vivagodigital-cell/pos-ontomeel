<?php
// api/config/database.php

// Helper to load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        error_log("Env file not found at: " . $path);
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv(sprintf('%s=%s', $name, $value));
        }
    }
}

// Load .env from root
loadEnv(__DIR__ . '/../../.env');

$host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$db   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'ontomeel_bookshop');
$user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');
$charset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
