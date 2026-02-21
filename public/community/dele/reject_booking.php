<?php
session_start();
include('../../../db_connect.php');

$res_id = $_GET['res_id'];
$community_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Sikkerhed: Slet kun hvis det er EJEREN der afviser
$sql = "DELETE r FROM reservations r 
        JOIN assets a ON r.asset_id = a.id 
        WHERE r.id = '$res_id' AND a.owner_id = '$user_id'";

$conn->query($sql);
header("Location: ./?id=" . $community_id);
exit();