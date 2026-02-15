<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Accept POST (form) or GET (link with code=XXX)
$raw = $_POST['invite_code'] ?? $_GET['code'] ?? '';
$invite_code = strtoupper(trim($raw));

if ($invite_code === '') {
    header("Location: index.php?join_error=invalid_code");
    exit();
}

$code_esc = $conn->real_escape_string($invite_code);
$row = $conn->query("SELECT id, name FROM communities WHERE invite_code = '$code_esc'")->fetch_assoc();

if (!$row) {
    header("Location: index.php?join_error=invalid_code");
    exit();
}

$community_id = $row['id'];

// Samme logik som join.php: tjek medlemsskab, tilføj hvis ny
$check = $conn->query("SELECT * FROM community_members WHERE user_id = '$user_id' AND community_id = '$community_id'");

if ($check->num_rows == 0) {
    $sql = "INSERT INTO community_members (user_id, community_id, role, alias_name) 
            VALUES ('$user_id', '$community_id', 'Medlem', '$user_name')";
    if ($conn->query($sql)) {
        header("Location: ../community/index.php?id=" . $community_id . "&joined=success");
        exit();
    } else {
        header("Location: index.php?join_error=db");
        exit();
    }
} else {
    header("Location: ../community/index.php?id=" . $community_id);
    exit();
}
