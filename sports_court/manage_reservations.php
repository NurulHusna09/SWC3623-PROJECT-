<?php
include 'db_connect.php';
session_start();

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all bookings with joined court & user data
$sql = "SELECT 
            b.booking_id, 
            c.court_name, 
            u.name AS user_name, 
            b.date, 
            b.start_time, 
            b.end_time, 
            b.total_price, 
            b.status
        FROM bookings b
        JOIN courts c ON b.court_id = c.court_id
        JOIN users u ON b.user_id = u.user_id
        ORDER BY b.date DESC, b.start_time ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Reservations - CourtBook</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }

body {
    background-color: #f0f8ff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* HEADER */
header {
    background: linear-gradient(90deg, #001f3f, #003f7f, #005f9f);
    color: white;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
h1 { font-size: 24px; }
.logout-btn {
    background: #00bfff;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.logout-btn:hover { background: #009acd; }

/* MAIN CONTENT */
.container {
    max-width: 1100px;
    margin: 50px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    padding: 20px 30px;
}
h2 {
    text-align: center;
    color: #005f9f;
    margin-bottom: 25px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background: #007bff;
    color: white;
}
tr:nth-child(even) { background-color: #f2f2f2; }

.status {
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
}
.pending { background: orange; }
.approved { background: green; }
.cancelled { background: red; }

.action-btn {
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    margin: 0 5px;
    display: inline-block;
}
.approve-btn { background: #00c851; }
.approve-btn:hover { background: #009e47; }
.cancel-btn { background: #ff4444; }
.cancel-btn:hover { background: #cc0000; }

/* FOOTER */
footer {
    background: #001f3f;
    color: white;
    text-align: center;
    padding: 12px;
    margin-top: auto;
    font-size: 14px;
}
</style>
</head>
<body>

<header>
    <h1>Manage Reservations</h1>
    <a href="admin_dashboard.php" class="logout-btn">← Back to Dashboard</a>
</header>

<div class="container">
    <h2>All Court Reservations</h2>
<?php
if (isset($_SESSION['success_msg'])) {
    echo "<div style='background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:15px;font-weight:bold;text-align:center;'>"
        . $_SESSION['success_msg'] . "</div>";
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:15px;font-weight:bold;text-align:center;'>"
        . $_SESSION['error_msg'] . "</div>";
    unset($_SESSION['error_msg']);
}
?>

    <table>
        <tr>
            <th>ID</th>
            <th>Court</th>
            <th>User</th>
            <th>Date</th>
            <th>Time</th>
            <th>Price (RM)</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $statusClass = strtolower($row['status']);
                echo "
                <tr>
                    <td>{$row['booking_id']}</td>
                    <td>{$row['court_name']}</td>
                    <td>{$row['user_name']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['start_time']} - {$row['end_time']}</td>
                    <td>RM{$row['total_price']}</td>
                    <td><span class='status $statusClass'>{$row['status']}</span></td>
                    <td>";
                
                if ($row['status'] == 'Pending') {
                    echo "
                        <a href='update_booking_status.php?id={$row['booking_id']}&status=Confirmed' class='action-btn approve-btn'>Approve</a>
                        <a href='update_booking_status.php?id={$row['booking_id']}&status=Cancelled' class='action-btn cancel-btn'>Cancel</a>
                    ";
                } else {
                    echo "-";
                }

                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No reservations found</td></tr>";
        }
        ?>
    </table>
</div>

<footer>© 2025 CourtBook. Admin Panel.</footer>

</body>
</html>
