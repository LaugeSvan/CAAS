<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function loadEnv($path) {
    if (!file_exists($path)) {
        die("Kritisk fejl: .env fil mangler på: " . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1], " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Bruger realpath baseret på db_connect.php's egen placering
loadEnv(realpath(__DIR__ . '/../.env'));

$host = $_ENV['DB_HOST'] ?? null;
$db   = $_ENV['DB_NAME'] ?? null;
$user = $_ENV['DB_USER'] ?? null;
$pass = $_ENV['DB_PASS'] ?? null;

if (!$host || !$db || !$user) {
    die("Server konfiguration mangler i .env");
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database forbindelse fejlede: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");