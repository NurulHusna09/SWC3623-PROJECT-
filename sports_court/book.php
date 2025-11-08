<?php
include 'db_connect.php';
session_start();

// Ensure user is logged in
if(!isset($_SESSION['user_id'])){
    $_SESSION['error_msg'] = "Please log in first!";
    header("Location: login.php");
    exit;
}

// Check court_id
if(!isset($_GET['court_id'])) {
    $_SESSION['error_msg'] = "No court selected!";
    header("Location: index.php");
    exit;
}

$court_id = intval($_GET['court_id']);

// Fetch court details securely
$stmtCourt = $conn->prepare("SELECT * FROM courts WHERE court_id=?");
$stmtCourt->bind_param("i", $court_id);
$stmtCourt->execute();
$courtResult = $stmtCourt->get_result();
$court = $courtResult->fetch_assoc();

if(!$court){
    $_SESSION['error_msg'] = "Court not found!";
    header("Location: index.php");
    exit;
}

// Default duration
$duration = 1;
$total_price = $court['price_per_hour'];

// Handle booking submission
if(isset($_POST['book_now'])){
    $user_id = $_SESSION['user_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $duration = intval($_POST['duration']);
    $price_per_hour = floatval($_POST['price_per_hour']);
    $total_price = $duration * $price_per_hour;

    // Calculate end time
    $end_time = date("H:i", strtotime($start_time) + $duration*3600);

    // Insert booking with status 'Pending'
    $stmt = $conn->prepare("INSERT INTO bookings (court_id, user_id, date, start_time, end_time, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iisssd", $court_id, $user_id, $date, $start_time, $end_time, $total_price);

    if($stmt->execute()){
        $_SESSION['success_msg'] = "Booking successful! Your reservation is pending confirmation.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error_msg'] = "Booking failed! Please try again.";
        header("Location: book.php?court_id=".$court_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book <?php echo htmlspecialchars($court['court_name']); ?> - CourtBook</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
* { box-sizing:border-box; margin:0; padding:0; font-family:'Roboto',sans-serif; }
html, body { height:100%; }
body {
    background-image: url('images/<?php echo strtolower($court['type']); ?>.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
}
.overlay {
    background-color: rgba(0,0,0,0.6);
    min-height: 100vh;
    padding: 50px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.booking-form {
    background: rgba(255,255,255,0.95);
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}
.booking-form h2 {
    text-align:center;
    margin-bottom:20px;
    color:#2e8b57;
}
.booking-form label { display:block; margin:10px 0 5px; font-weight:bold; }
.booking-form input, .booking-form select {
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    margin-bottom:15px;
}
.booking-form button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background-color:#1e90ff;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}
.booking-form button:hover { background-color:#009acd; }
.total-price { font-size:18px; font-weight:bold; margin-bottom:15px; }

/* Alert Messages */
.alert { padding:15px; margin-bottom:20px; border-radius:8px; text-align:center; font-weight:bold; }
.alert.success { background-color:#32cd32; color:white; }
.alert.error { background-color:#ff4500; color:white; }
</style>
<script>
function updatePrice() {
    const duration = document.getElementById('duration').value;
    const pricePerHour = <?php echo $court['price_per_hour']; ?>;
    document.getElementById('totalPrice').innerText = "Total Price: RM" + (duration * pricePerHour);
}
</script>
</head>
<body>
<div class="overlay">
    <form class="booking-form" method="POST" action="book.php?court_id=<?php echo $court_id; ?>">
        <h2>Book <?php echo htmlspecialchars($court['court_name']); ?></h2>

        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="alert error"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
        <?php endif; ?>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Start Time:</label>
        <input type="time" name="start_time" required>

        <label>Duration (hours):</label>
        <select name="duration" id="duration" onchange="updatePrice()">
            <?php for($i=1;$i<=5;$i++): ?>
                <option value="<?php echo $i; ?>" <?php if($i==$duration) echo 'selected'; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>

        <p class="total-price" id="totalPrice">Total Price: RM<?php echo $total_price; ?></p>

        <input type="hidden" name="court_id" value="<?php echo $court['court_id']; ?>">
        <input type="hidden" name="price_per_hour" value="<?php echo $court['price_per_hour']; ?>">

        <button type="submit" name="book_now">Book Now</button>
    </form>
</div>

<script> updatePrice(); </script>
</body>
</html>
