<?php
session_start();
include('../../db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !isset($_POST['id']) || !isset($_POST['event_id']) || !isset($_POST['action'])) {
    header("Location: ../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_POST['id']);
$event_id = (int)$_POST['event_id'];
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] === 'join' ? 'join' : 'leave';

// Tjek at eventet tilhører community og bruger er medlem
$check = $conn->query("SELECT e.id FROM events e 
                       JOIN community_members m ON m.community_id = e.community_id AND m.user_id = '$user_id'
                       WHERE e.id = '$event_id' AND e.community_id = '$community_id'");
if ($check->num_rows == 0) {
    header("Location: view.php?id=" . $community_id . "&event_id=" . $event_id);
    exit();
}

if ($action === 'join') {
    $cnt = $conn->query("SELECT COUNT(*) as c FROM event_attendees WHERE event_id = '$event_id'")->fetch_assoc()['c'];
    $max = $conn->query("SELECT max_attendees FROM events WHERE id = '$event_id'")->fetch_assoc()['max_attendees'];
    if ($max !== null && (int)$max <= (int)$cnt) {
        header("Location: view.php?id=" . $community_id . "&event_id=" . $event_id . "&full=1");
        exit();
    }
    $conn->query("INSERT IGNORE INTO event_attendees (event_id, user_id) VALUES ('$event_id', '$user_id')");
} else {
    $conn->query("DELETE FROM event_attendees WHERE event_id = '$event_id' AND user_id = '$user_id'");
}

header("Location: view.php?id=" . $community_id . "&event_id=" . $event_id);
exit();
