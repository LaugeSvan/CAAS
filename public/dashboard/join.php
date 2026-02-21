<?php
session_start();
// Da vi er i /dashboard/, skal vi kun ét niveau op for at ramme rodmappen
include('../db_connect.php'); 

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ./");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$community_id = $conn->real_escape_string($_GET['id']);

// 1. Tjek om brugeren allerede er medlem
$check = $conn->query("SELECT * FROM community_members WHERE user_id = '$user_id' AND community_id = '$community_id'");

if ($check->num_rows == 0) {
    // 2. Tilføj som medlem
    // Vi bruger deres rigtige navn som standard 'alias_name'
    $sql = "INSERT INTO community_members (user_id, community_id, role, alias_name) 
            VALUES ('$user_id', '$community_id', 'Medlem', '$user_name')";
    
    if ($conn->query($sql)) {
        // Succes: Send dem direkte ind i det nye community
        header("Location: ../community/index.php?id=" . $community_id . "&joined=success");
        exit();
    } else {
        die("Kunne ikke melde dig ind: " . $conn->error);
    }
} else {
    // Hvis de allerede er medlem, sender vi dem bare derhen
    header("Location: ../community/index.php?id=" . $community_id);
    exit();
}
?>