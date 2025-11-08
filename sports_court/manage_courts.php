<?php
include 'db_connect.php';
session_start();

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all courts
$sql = "SELECT * FROM courts ORDER BY court_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Courts - CourtBook</title>
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
    max-width: 1000px;
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
    margin-top: 10px;
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

.action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    margin: 0 5px;
    display: inline-block;
}
.edit-btn { background: #00bfff; }
.edit-btn:hover { background: #009acd; }
.delete-btn { background: #ff4d4d; }
.delete-btn:hover { background: #e60000; }
.status-btn {
    background: #00c851;
    padding: 6px 10px;
    border-radius: 5px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    border: none;
}
.status-btn.maintenance { background: #ffbb33; }

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
    <h1>Manage Courts</h1>
    <a href="admin_dashboard.php" class="logout-btn">← Back to Dashboard</a>
</header>

<div class="container">
    <h2>Courts Overview</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Court Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Price/hour (RM)</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $status = $row['status'] == 'available' ? 'Available' : 'Maintenance';
                $statusClass = $row['status'] == 'available' ? '' : 'maintenance';
                echo "
                <tr>
                    <td>{$row['court_id']}</td>
                    <td>{$row['court_name']}</td>
                    <td>{$row['type']}</td>
                    <td>
                        <form method='POST' action='update_court_status.php' style='display:inline;'>
                            <input type='hidden' name='court_id' value='{$row['court_id']}'>
                            <input type='hidden' name='current_status' value='{$row['status']}'>
                            <button type='submit' class='status-btn $statusClass'>$status</button>
                        </form>
                    </td>
                    <td>{$row['price_per_hour']}</td>
                    <td>
                        <a href='edit_court.php?id={$row['court_id']}' class='action-btn edit-btn'>Edit</a>
                        <a href='delete_court.php?id={$row['court_id']}' class='action-btn delete-btn' onclick='return confirm(\"Delete this court?\")'>Delete</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No courts found</td></tr>";
        }
        ?>
    </table>
</div>

<footer>© 2025 CourtBook. Admin Panel.</footer>

</body>
</html>
