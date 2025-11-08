<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
    $_SESSION['error_msg'] = "Please log in first!";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all courts
$sql = "SELECT * FROM courts";
$result = $conn->query($sql);

// Fetch user info for header
$userData = $conn->query("SELECT name, profile_img FROM users WHERE user_id = $user_id")->fetch_assoc();
$userName = $userData['name'] ?? $_SESSION['name'];
$profileImg = $userData['profile_img'] ?? "profile_default.png";
$profilePath = "uploads/" . $profileImg;
if (!file_exists($profilePath)) $profilePath = "uploads/profile_default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CourtBook - Courts</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Roboto',sans-serif;}
html, body {
    height: 100%;
}
body {
    background-color:#f0f8ff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    overflow-x:hidden;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:0;left:-260px;
    width:250px;height:100%;
    background:linear-gradient(180deg,#001f3f,#0077b6,#00bfff);
    color:white;padding:20px;
    transition:0.3s;
    z-index:1000;
}
.sidebar.active{left:0;}
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar a{display:block;color:white;text-decoration:none;padding:12px;border-radius:8px;margin:8px 0;transition:0.3s;}
.sidebar a:hover{background-color:rgba(255,255,255,0.2);}
.close-btn{position:absolute;right:15px;top:15px;font-size:20px;cursor:pointer;}

/* HEADER */
header {
    width: 100%;
    background: linear-gradient(90deg, #001f3f, #003f7f, #005f9f);
    color: white;
    padding: 10px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    position: relative; /* key to center title */
}

header img {
    height: 45px;
    cursor: pointer;
    transition: transform 0.3s;
}
header img:hover {
    transform: scale(1.1);
}

/* 🎯 Center the title perfectly */
header h1 {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-size: 24px;
    margin: 0;
    text-align: center;
}

/* Profile area on the right */
.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.profile-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
    object-fit: cover;
}

.login-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.login-buttons span {
    color: white;
    font-weight: 500;
}

.logout-btn {
    background: #00bfff;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}

.logout-btn:hover {
    background: #009acd;
}
.header-right{display:flex;align-items:center;gap:15px;}
.profile-avatar{width:40px;height:40px;border-radius:50%;border:2px solid white;object-fit:cover;}
.login-buttons{display:flex;align-items:center;gap:10px;}
.login-buttons span{color:white;font-weight:500;}
.logout-btn{background:#00bfff;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-weight:bold;transition:0.3s;}
.logout-btn:hover{background:#009acd;}

/* COURT GRID */
.main-content {
    flex: 1; /* pushes footer to bottom */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.container{
    max-width:1200px;
    margin:60px auto;
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(260px,1fr));
    gap:25px;
    padding:20px;
}
.card{
    background:white;
    border-radius:15px;
    box-shadow:0 6px 15px rgba(0,0,0,0.15);
    padding:18px;
    text-align:center;
    transition:0.3s;
    animation:fadeIn 0.8s ease-in-out;
}
.card:hover{transform:translateY(-6px);box-shadow:0 12px 25px rgba(0,0,0,0.25);}
.card img{width:100%;height:160px;object-fit:cover;border-radius:12px;margin-bottom:12px;}
.card h2{color:#0077b6;margin-bottom:8px;font-size:20px;}
.card p{margin:4px 0;font-weight:500;color:#333;}
.available{color:green;font-weight:bold;}
.maintenance{color:red;font-weight:bold;}
.book-btn{
    display:inline-block;
    margin-top:12px;
    padding:10px 18px;
    background-color:#00bfa5;
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}
.book-btn:hover{background-color:#009e85;transform:scale(1.05);}
.unavailable{background:#aaa !important;cursor:not-allowed;}
@keyframes fadeIn{from{opacity:0;transform:translateY(15px);}to{opacity:1;transform:translateY(0);}}

/* FOOTER */
footer{
    background:#001f3f;
    color:white;
    padding:15px 40px;
    text-align:right;
    font-size:15px;
    margin-top:auto;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <span class="close-btn" onclick="toggleSidebar()">×</span>
    <h2>CourtBook</h2>
    <a href="index.php">🏠 Dashboard</a>
    <a href="courts.php">🏀 Courts</a>
    <a href="about.php">ℹ️ About Us</a>
    <a href="contact.php">📧 Contact</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- HEADER -->
<header>
    <img src="images/logo1.png" alt="CourtBook Logo" onclick="toggleSidebar()">
    <h1>CourtBook</h1>
    <div class="header-right">
        <img src="<?= htmlspecialchars($profilePath) ?>" class="profile-avatar" alt="Profile">
        <div class="login-buttons">
            <span>Hello, <strong><?= htmlspecialchars($userName) ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="container">
        <?php
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $status_class = ($row['status']=='available') ? 'available' : 'maintenance';
                $img_file = "images/".$row['type'].".png";
                if(!file_exists($img_file)) $img_file="images/default.png";
                echo "
                <div class='card'>
                    <img src='$img_file' alt='".$row['type']."'>
                    <h2>".$row['court_name']."</h2>
                    <p>Type: ".$row['type']."</p>
                    <p>Status: <span class='$status_class'>".$row['status']."</span></p>
                    <p>Price/hour: RM".$row['price_per_hour']."</p>";
                if($row['status']=='available'){
                    echo "<a class='book-btn' href='book.php?court_id=".$row['court_id']."'>Book Now</a>";
                } else {
                    echo "<a class='book-btn unavailable'>Unavailable</a>";
                }
                echo "</div>";
            }
        }else{
            echo "<p style='grid-column:1/-1;text-align:center;'>No courts found</p>";
        }
        $conn->close();
        ?>
    </div>
</div>

<!-- FOOTER -->
<footer>
    © 2025 CourtBook. All rights reserved.
</footer>

<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("active");
}
</script>
</body>
</html>
