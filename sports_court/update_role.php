<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['user_id']) && isset($_POST['current_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['current_role'] == 'admin' ? 'player' : 'admin';

    $stmt = $conn->prepare("UPDATE users SET role=? WHERE user_id=?");
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
}

header("Location: manage_users.php");
exit;
?>
