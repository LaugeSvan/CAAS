<?php
session_start();
include('../../../db_connect.php'); // Sørg for at stien passer til din filstruktur

if (!isset($_GET['id']) || !isset($_GET['res_id'])) {
    header("Location: ../../");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$res_id = $conn->real_escape_string($_GET['res_id']);
$user_id = $_SESSION['user_id'];

// SIKKERHED: Vi sletter kun reservationen, hvis den tilhører den loggede bruger.
// Det forhindrer folk i at slette hinandens bookinger ved at gætte et ID i URL'en.
$sql = "DELETE FROM reservations WHERE id = '$res_id' AND user_id = '$user_id'";

if ($conn->query($sql) === TRUE) {
    // Vi sender brugeren tilbage med en status, så vi kan vise den pæne besked-boks
    header("Location: ./?id=" . $community_id . "&status=cancelled");
    exit();
} else {
    // Hvis noget går galt (f.eks. databasefejl)
    die("Fejl ved aflysning: " . $conn->error);
}
?>