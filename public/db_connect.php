<?php
// Slå fejlvisning til herinde også for en sikkerheds skyld
ini_set('display_errors', 1);
error_reporting(E_ALL);

function loadEnv($path) {
    if (!file_exists($path)) {
        // I stedet for throw, så brug die for at se fejlen direkte
        die("Fejl: .env filen kunne ikke findes på stien: " . $path);
    }
    
    // ... resten af din loadEnv kode er fin ...
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $key = trim($parts[0]);
        $value = trim($parts[1], " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Brug DOCUMENT_ROOT til at finde .env uden for /public
// Hvis din .env ligger én mappe OVER 'public', skal stien være:
$envPath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../.env');

loadEnv($envPath);

// Resten af din MySQLi kode...
$host = $_ENV['DB_HOST'] ?? null;
$db   = $_ENV['DB_NAME'] ?? null;
$user = $_ENV['DB_USER'] ?? null;
$pass = $_ENV['DB_PASS'] ?? null;

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}