<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$adminName = $_SESSION['name'];
// Dashboard statistics
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$totalBookings = $conn->query("SELECT COUNT(*) AS count FROM bookings")->fetch_assoc()['count'];
$totalCourts = $conn->query("SELECT COUNT(*) AS count FROM courts")->fetch_assoc()['count'];

// Booking Chart (past 7 days)
$bookingChartData = $conn->query("
    SELECT DATE(date) AS day, COUNT(*) AS count
    FROM bookings
    WHERE DATE(date) >= CURDATE() - INTERVAL 6 DAY
    GROUP BY day
    ORDER BY day ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - CourtBook</title>
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
header img {
    height: 45px;
    cursor: pointer;
    transition: transform 0.3s;
}
header img:hover { transform: scale(1.1); }
h1 { flex: 1; text-align: center; font-size: 24px; }
.header-right { display: flex; align-items: center; gap: 15px; }
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

/* CONTAINER */
.container {
    max-width: 1000px;
    margin: 60px auto;
    text-align: center;
}
h2 {
    color: #005f9f;
    margin-bottom: 20px;
}
.admin-buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 25px;
}
.admin-buttons a {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 20px 30px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    transition: 0.3s;
}
.admin-buttons a:hover {
    background: #005fa3;
    transform: scale(1.05);
}
.dashboard-cards {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    margin: 30px auto;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 25px;
    width: 260px;
    text-align: center;
    transition: transform 0.3s;
}

.card h3 {
    color: #0077b6;
    margin-bottom: 10px;
}

.card p {
    font-size: 2rem;
    color: #005f9f;
    font-weight: bold;
}

.card:hover {
    transform: translateY(-6px);
}

.chart-section {
    text-align: center;
    margin-bottom: 40px;
}


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

<!-- HEADER -->
<header>
    <img src="images/logo1.png" alt="CourtBook Logo">
    <h1>Admin Dashboard</h1>
    <div class="header-right">
        <span>Hello, <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<!-- MAIN -->
<div class="container">
    <h2>Welcome, Admin 👋</h2>
    <p style="margin-bottom: 30px;">Manage everything from here efficiently.</p>

    <!-- DASHBOARD SUMMARY CARDS -->
<div class="dashboard-cards">
    <div class="card">
        <h3>👥 Total Users</h3>
        <p><?php echo $totalUsers; ?></p>
    </div>
    <div class="card">
        <h3>🏟 Total Courts</h3>
        <p><?php echo $totalCourts; ?></p>
    </div>
    <div class="card">
        <h3>📅 Total Bookings</h3>
        <p><?php echo $totalBookings; ?></p>
    </div>
</div>

<!-- CHART SECTION -->
<div class="chart-section">
    <h3 style="color:#005f9f;">Bookings (Past 7 Days)</h3>
    <canvas id="bookingChart" style="max-width:600px; margin:auto;"></canvas>
</div>

<!-- MANAGEMENT BUTTONS -->
<div class="admin-buttons">
    <a href="manage_courts.php">🏟 Manage Courts</a>
    <a href="manage_users.php">👤 Manage Users</a>
    <a href="manage_reservations.php">📅 Manage Reservations</a>
</div>
</div>

<!-- FOOTER -->
<footer>© 2025 CourtBook. Admin Panel.</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php
$labels=[]; $data=[];
while($row = $bookingChartData->fetch_assoc()) {
    $labels[] = $row['day'];
    $data[] = $row['count'];
}
?>
const ctx = document.getElementById('bookingChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Bookings',
            data: <?= json_encode($data) ?>,
            backgroundColor: '#007bff'
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>


</body>
</html>
