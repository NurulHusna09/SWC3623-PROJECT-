<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['court_id']) && isset($_POST['current_status'])) {
    $court_id = $_POST['court_id'];
    $new_status = $_POST['current_status'] == 'available' ? 'maintenance' : 'available';

    $stmt = $conn->prepare("UPDATE courts SET status=? WHERE court_id=?");
    $stmt->bind_param("si", $new_status, $court_id);
    $stmt->execute();
}

header("Location: manage_courts.php");
exit;
?>
