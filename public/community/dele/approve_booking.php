<?php
session_start();
include('../../../db_connect.php');

if (!isset($_GET['id']) || !isset($_GET['res_id'])) {
    header("Location: ../../");
    exit();
}

$res_id = $conn->real_escape_string($_GET['res_id']);
$community_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

// 1. Hent info om den booking vi er ved at godkende (for at kende datoer og asset_id)
$info_sql = "SELECT asset_id, start_date, end_date FROM reservations WHERE id = '$res_id'";
$info_res = $conn->query($info_sql);
$booking = $info_res->fetch_assoc();

if ($booking) {
    $asset_id = $booking['asset_id'];
    $start = $booking['start_date'];
    $end = $booking['end_date'];

    // 2. Godkend denne reservation
    $approve_sql = "UPDATE reservations r 
                    JOIN assets a ON r.asset_id = a.id 
                    SET r.status = 'confirmed' 
                    WHERE r.id = '$res_id' AND a.owner_id = '$user_id'";
    
    if ($conn->query($approve_sql)) {
        
        // 3. AUTO-REJECT: Slet alle andre PENDING anmodninger der overlapper med disse datoer
        // Vi sletter dem, fordi de ikke længere kan lade sig gøre
        $cleanup_sql = "DELETE FROM reservations 
                        WHERE asset_id = '$asset_id' 
                        AND status = 'pending' 
                        AND id != '$res_id'
                        AND (
                            ('$start' BETWEEN start_date AND end_date) OR 
                            ('$end' BETWEEN start_date AND end_date) OR
                            (start_date BETWEEN '$start' AND '$end')
                        )";
        
        $conn->query($cleanup_sql);
    }
}

header("Location: ./?id=" . $community_id . "&status=approved");
exit();