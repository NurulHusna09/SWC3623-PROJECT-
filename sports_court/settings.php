<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user info
$user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();

$success = "";
$error = "";

// Handle form submission
if (isset($_POST['update'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $profile_img = $user['profile_img'] ?? "profile_default.png";

    // ✅ Handle image upload to uploads/ folder
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // auto-create if missing
        }

        $file_name = "user_" . $user_id . "_" . time() . "_" . basename($_FILES["profile_img"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
                $profile_img = $file_name;
            } else {
                $error = "❌ Failed to upload file.";
            }
        } else {
            $error = "❌ Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    // ✅ Update user info
    if (empty($error)) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET name='$name', email='$email', password='$hashed', profile_img='$profile_img' WHERE user_id='$user_id'");
        } else {
            $conn->query("UPDATE users SET name='$name', email='$email', profile_img='$profile_img' WHERE user_id='$user_id'");
        }

        $success = "✅ Profile updated successfully!";
        $_SESSION['name'] = $name;
        $user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings - CourtBook</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #00bfff, #1e90ff, #001f3f);
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.settings-box {
    background: white;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    width: 400px;
}
.settings-box h2 {
    text-align: center;
    color: #1e90ff;
    margin-bottom: 20px;
}
.settings-box img {
    display: block;
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto 15px auto;
    border: 3px solid #00bfff;
}
.settings-box input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
}
.settings-box button {
    width: 100%;
    padding: 10px;
    background: #1e90ff;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}
.settings-box button:hover {
    background: #009acd;
}
.success {
    background: #c8f7c5;
    color: green;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.error {
    background: #ffd1d1;
    color: red;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.back-link {
    text-align: center;
    margin-top: 15px;
}
.back-link a {
    color: #1e90ff;
    text-decoration: none;
}
.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="settings-box">
    <h2>Profile Settings</h2>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_img'] ?? 'profile_default.png'); ?>" alt="Profile Image">
        <input type="file" name="profile_img" accept="image/*">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Full Name" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
        <button type="submit" name="update">Update Profile</button>
    </form>

    <div class="back-link">
        <a href="index.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
