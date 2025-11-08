<?php
include 'db_connect.php';

$newPassword = 'admin123';
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

// Update admin password in database
$sql = "UPDATE users SET password='$hashed' WHERE email='admin@gmail.com'";

if ($conn->query($sql)) {
    echo "<h2 style='color:green;'>✅ Admin password successfully reset!</h2>";
    echo "<p>New password: <strong>$newPassword</strong></p>";
    echo "<p>Stored hash: <code>$hashed</code></p>";
} else {
    echo "<h2 style='color:red;'>❌ Failed to reset password:</h2> " . $conn->error;
}
?>
