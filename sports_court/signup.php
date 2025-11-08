<?php
include 'db_connect.php';
session_start();

if(isset($_POST['signup'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'player'; // default role

    // Check if email already exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $conn->query("INSERT INTO users (name,email,password,role) VALUES ('$name','$email','$password','$role')");
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['name'] = $name;
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - CourtBook</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background:#f0f8ff; display:flex; justify-content:center; align-items:center; height:100vh; }
        .signup-box { background:white; padding:30px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.2); width:350px; }
        .signup-box h2 { text-align:center; margin-bottom:20px; color:#1e90ff; }
        .signup-box input { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
        .signup-box button { width:100%; padding:10px; background:#1e90ff; color:white; font-weight:bold; border:none; border-radius:6px; cursor:pointer; transition:0.3s; }
        .signup-box button:hover { background:#009acd; }
        .error { color:red; text-align:center; margin-bottom:10px; }
        .login-link { text-align:center; margin-top:10px; }
        .login-link a { color:#1e90ff; text-decoration:none; }
    </style>
</head>
<body>
    <div class="signup-box">
        <h2>Create Account</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
