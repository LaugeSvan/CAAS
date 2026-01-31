<?php
session_start();
include('../../db_connect.php');

if (!isset($_GET['id']) || !isset($_GET['asset_id'])) {
    header("Location: ../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$asset_id = $conn->real_escape_string($_GET['asset_id']);
$user_id = $_SESSION['user_id'];

// 1. Start med at tjekke om brugeren rent faktisk ejer tingen
$check = $conn->query("SELECT id FROM assets WHERE id = '$asset_id' AND owner_id = '$user_id'");

if ($check->num_rows > 0) {
    // 2. Slet alle reservationer tilknyttet denne ting først (så vi rydder op)
    $conn->query("DELETE FROM reservations WHERE asset_id = '$asset_id'");

    // 3. Slet selve tingen
    $sql = "DELETE FROM assets WHERE id = '$asset_id' AND owner_id = '$user_id' AND community_id = '$community_id'";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ./?id=" . $community_id . "&status=asset_deleted");
        exit();
    } else {
        die("Fejl ved sletning: " . $conn->error);
    }
} else {
    die("Du har ikke tilladelse til at slette denne genstand.");
}
?>