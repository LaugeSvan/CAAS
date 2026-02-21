<?php

// Load .env from OUTSIDE public
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new RuntimeException('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1], " \t\n\r\0\x0B\"'");

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// IMPORTANT: go one level up from /public
loadEnv(__DIR__ . '/../.env');

// Read env vars
$host = $_ENV['DB_HOST'] ?? null;
$db   = $_ENV['DB_NAME'] ?? null;
$user = $_ENV['DB_USER'] ?? null;
$pass = $_ENV['DB_PASS'] ?? null;

// Fail early if something is missing
if (!$host || !$db || !$user) {
    error_log('Database env vars missing');
    die('Server configuration error');
}

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Handle connection errors safely
if ($conn->connect_error) {
    error_log('DB connection failed: ' . $conn->connect_error);
    die('Database connection failed');
}