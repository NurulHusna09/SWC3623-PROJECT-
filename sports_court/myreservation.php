<?php
include 'db_connect.php';
session_start();

// Make sure user is logged in
if(!isset($_SESSION['user_id'])){
    echo "<script>alert('Please log in first!'); window.location='login.php';</script>";
    exit;
}

// Handle cancel booking
if(isset($_GET['cancel_id'])){
    $cancel_id = intval($_GET['cancel_id']);
    // Update only if this booking belongs to logged-in user and is pending
    $stmt = $conn->prepare("UPDATE booking SET status='Cancelled' WHERE booking_id=? AND user_id=? AND status='Pending'");
    $stmt->bind_param("ii", $cancel_id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: myreservation.php"); // refresh page
    exit;
}

// Fetch all bookings for logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT b.booking_id, c.court_name, b.date, b.start_time, b.end_time, b.total_price, b.status
        FROM booking b
        JOIN courts c ON b.court_id = c.court_id
        WHERE b.user_id = ?
        ORDER BY b.date DESC, b.start_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reservations - CourtBook</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: #f0f2f5; padding: 20px; }
        h2 { text-align: center; color: #2e8b57; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #1e90ff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .btn-cancel { padding: 5px 10px; background-color: #ff4d4d; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-cancel:hover { background-color: #e60000; }
    </style>
</head>
<body>

    <h2>My Reservations</h2>

    <table>
        <thead>
            <tr>
                <th>Court Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Total Price (RM)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['court_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_time'] . " - " . $row['end_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo number_format($row['total_price'], 2); ?></td>
                        <td>
                            <?php if($row['status'] == 'Pending'): ?>
                                <a href="myreservation.php?cancel_id=<?php echo $row['booking_id']; ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No bookings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
