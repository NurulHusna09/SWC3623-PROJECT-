<?php
include 'db_connect.php';
session_start();

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();



        // ✅ Securely verify hashed password

        if (password_verify($password, $user['password'])) {
            // ✅ Store session info
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role']; // ✅ store role too
            $_SESSION['profile_img'] = $user['profile_img'];

            // ✅ Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not registered!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - CourtBook</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background:#f0f8ff; display:flex; justify-content:center; align-items:center; height:100vh; }
        .login-box { background:white; padding:30px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.2); width:350px; }
        .login-box h2 { text-align:center; margin-bottom:20px; color:#1e90ff; }
        .login-box input { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
        .login-box button { width:100%; padding:10px; background:#1e90ff; color:white; font-weight:bold; border:none; border-radius:6px; cursor:pointer; transition:0.3s; }
        .login-box button:hover { background:#009acd; }
        .error { color:red; text-align:center; margin-bottom:10px; }
        .signup-link { text-align:center; margin-top:10px; }
        .signup-link a { color:#1e90ff; text-decoration:none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </div>
</body>
</html>
