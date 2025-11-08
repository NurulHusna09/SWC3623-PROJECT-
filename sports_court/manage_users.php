<?php
include 'db_connect.php';
session_start();

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY user_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - CourtBook</title>
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
.role-btn {
    background: #00c851;
    padding: 6px 10px;
    border-radius: 5px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    border: none;
}
.role-btn.admin { background: #ffbb33; }

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
    <h1>Manage Users</h1>
    <a href="admin_dashboard.php" class="logout-btn">← Back to Dashboard</a>
</header>

<div class="container">
    <h2>Registered Users</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Profile</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $role = ucfirst($row['role']);
                $roleClass = $row['role'] == 'admin' ? 'admin' : '';
                $profilePath = !empty($row['profile_img']) ? "uploads/" . $row['profile_img'] : "uploads/profile_default.png";
                echo "
                <tr>
                    <td>{$row['user_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <form method='POST' action='update_role.php' style='display:inline;'>
                            <input type='hidden' name='user_id' value='{$row['user_id']}'>
                            <input type='hidden' name='current_role' value='{$row['role']}'>
                            <button type='submit' class='role-btn $roleClass'>$role</button>
                        </form>
                    </td>
                    <td><img src='$profilePath' alt='Profile' style='width:40px;height:40px;border-radius:50%;object-fit:cover;'></td>
                    <td>
                        <a href='edit_user.php?id={$row['user_id']}' class='action-btn edit-btn'>Edit</a>
                        <a href='delete_user.php?id={$row['user_id']}' class='action-btn delete-btn' onclick='return confirm(\"Delete this user?\")'>Delete</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No users found</td></tr>";
        }
        ?>
    </table>
</div>

<footer>© 2025 CourtBook. Admin Panel.</footer>

</body>
</html>
