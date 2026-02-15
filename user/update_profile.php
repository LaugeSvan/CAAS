<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

$error = '';

if ($name === '') {
    $error = 'Navn må ikke være tomt.';
} elseif ($email === '') {
    $error = 'E-mail må ikke være tomt.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Ugyldig e-mail adresse.';
}

if ($error === '') {
    $name_esc = $conn->real_escape_string($name);
    $email_esc = $conn->real_escape_string($email);

    // Hvis e-mail er ændret, tjek at den ikke bruges af en anden
    $existing = $conn->query("SELECT id FROM users WHERE email = '$email_esc' AND id != '$user_id'");
    if ($existing && $existing->num_rows > 0) {
        $error = 'Denne e-mail er allerede i brug.';
    } else {
        $sql = "UPDATE users SET name = '$name_esc', email = '$email_esc' WHERE id = '$user_id'";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['user_name'] = $name;
            header("Location: index.php?updated=1");
            exit();
        } else {
            $error = 'Der skete en fejl. Prøv igen.';
        }
    }
}

// Ved fejl: send tilbage til profil med fejlbesked
header("Location: index.php?error=" . urlencode($error));
exit();
