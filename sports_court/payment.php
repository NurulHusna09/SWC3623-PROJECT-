<?php
include 'db_connect.php';

if(!isset($_GET['booking_id'])){
    echo "No booking selected!";
    exit;
}

$booking_id = $_GET['booking_id'];

// Fetch booking info
$booking = $conn->query("SELECT b.*, c.court_name FROM bookings b JOIN courts c ON b.court_id=c.court_id WHERE b.booking_id='$booking_id'")->fetch_assoc();

// Handle "payment" submission
if(isset($_POST['pay'])){
    $method = $_POST['payment_method']; // just store for display, optional
    // Update status to Confirmed
    $conn->query("UPDATE bookings SET status='Confirmed' WHERE booking_id='$booking_id'");
    header("Location: dashboard.php"); // back to dashboard
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment - <?php echo $booking['court_name']; ?></title>
<style>
body { font-family: Arial,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:#f0f8ff; }
.payment-form { background: rgba(255,255,255,0.95); padding:30px; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.3); width:350px; text-align:center; }
.payment-form h2 { margin-bottom:20px; }
.payment-form button { margin-top:15px; padding:10px 20px; background:#1e90ff; color:white; border:none; border-radius:6px; cursor:pointer; width:100%; }
.payment-form button:hover { background:#009acd; }
.payment-form select { padding:8px; width:100%; border-radius:6px; margin-top:10px; }
</style>
</head>
<body>

<div class="payment-form">
    <h2>Payment for <?php echo $booking['court_name']; ?></h2>
    <p>Total Price: RM <?php echo number_format($booking['total_price'],2); ?></p>

    <form method="POST">
        <label>Choose Payment Method:</label>
        <select name="payment_method" required>
            <option value="Visa">Visa</option>
            <option value="Debit">Debit</option>
            <option value="Touch n Go">Touch 'n Go</option>
        </select>

        <button type="submit" name="pay">Pay Now</button>
    </form>
</div>

</body>
</html>
