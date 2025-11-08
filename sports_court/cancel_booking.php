<?php
include 'db_connect.php';
session_start();

// Check if user logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Check if booking ID exists
if(!isset($_GET['id'])){
    $_SESSION['error_msg'] = "Invalid booking selected!";
    header("Location: index.php");
    exit;
}

$booking_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Confirm booking belongs to user
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    $_SESSION['error_msg'] = "You cannot cancel this booking.";
    header("Location: index.php");
    exit;
}

// Update status to Cancelled
$update = $conn->prepare("UPDATE bookings SET status='Cancelled' WHERE booking_id=?");
$update->bind_param("i", $booking_id);
if($update->execute()){
    $_SESSION['success_msg'] = "Booking successfully cancelled!";
} else {
    $_SESSION['error_msg'] = "Error cancelling booking. Please try again.";
}

header("Location: index.php");
exit;
?>
