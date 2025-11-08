<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $booking_id = intval($_GET['id']);
    $new_status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE booking_id=?");
    $stmt->bind_param("si", $new_status, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Booking status updated to <strong>$new_status</strong> successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to update booking status. Please try again.";
    }
}

header("Location: manage_reservations.php");
exit;
?>
