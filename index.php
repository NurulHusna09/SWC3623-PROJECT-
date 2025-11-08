<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// My Reservations
$reservations = [];
$stmt = $conn->prepare("
    SELECT b.booking_id, b.date, b.start_time, b.end_time, b.status, b.total_price, c.court_name
    FROM bookings b
    JOIN courts c ON b.court_id = c.court_id
    WHERE b.user_id = ?
    ORDER BY b.date DESC, b.start_time ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    $reservations[] = $row;
}

// Trending Court
$trendingCourt = $conn->query("
    SELECT c.court_name, COUNT(*) as count
    FROM bookings b
    JOIN courts c ON b.court_id = c.court_id
    WHERE b.status='Confirmed'
    GROUP BY b.court_id
    ORDER BY count DESC
    LIMIT 1
")->fetch_assoc();

// Booking Chart Data
$bookingChartData = $conn->query("
    SELECT DATE(date) as day, COUNT(*) as count
    FROM bookings
    WHERE status='Confirmed'
    AND DATE(date) >= CURDATE() - INTERVAL 6 DAY
    GROUP BY day
    ORDER BY day ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CourtBook - Sports Court Reservation</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Roboto',sans-serif;}
body{background-color:#f0f8ff;overflow-x:hidden;}

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
    position: relative;
}

/* ✨ Only apply gold animation to the logo */
header img.logo {
    height: 45px;
    cursor: pointer;
    transition: transform 0.3s, filter 0.3s;
    animation: goldPulse 2s infinite ease-in-out;
    filter: drop-shadow(0 0 6px gold);
}

/* Hover effect only for logo */
header img.logo:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 12px #ffd700);
}

/* ✨ Gold pulse animation keyframes */
@keyframes goldPulse {
    0% { transform: scale(1); filter: drop-shadow(0 0 5px #ffd700); }
    50% { transform: scale(1.08); filter: drop-shadow(0 0 15px #ffec8b); }
    100% { transform: scale(1); filter: drop-shadow(0 0 5px #ffd700); }
}
/* --- Center the title perfectly --- */
header h1 {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-size: 24px;
    margin: 0;
}

/* Right-side profile & buttons */
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

/* PROFILE + LOGIN combined layout */
.profile-and-login {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Move picture a bit closer to text */
.profile-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 2px solid #fff;
    object-fit: cover;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 0 8px #00ffff;
}

/* PROFILE */
.profile-section {
    display:flex;
    align-items:center;
    gap:10px;
    color:white;
    cursor:pointer;
    position:relative;
}
.profile-avatar {
    width:40px;
    height:40px;
    border-radius:50%;
    border:2px solid white;
    object-fit:cover;
    transition:transform 0.3s ease, box-shadow 0.3s ease;
}
.profile-avatar:hover {
    transform:scale(1.1);
    box-shadow:0 0 8px #00ffff;
}
.profile-dropdown {
    display:none;
    position:absolute;
    top:50px;
    right:0;
    background:white;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    overflow:hidden;
    min-width:150px;
    z-index:10;
}
.profile-dropdown a {
    display:block;
    padding:10px;
    color:#333;
    text-decoration:none;
    font-weight:500;
    transition:background 0.3s;
}
.profile-dropdown a:hover { background-color:#f0f8ff; }

/* SIDEBAR */
.sidebar{position:fixed;top:0;left:-260px;width:250px;height:100%;
background:linear-gradient(180deg,#001f3f,#0077b6,#00bfff);color:white;
transition:0.3s;padding:20px;z-index:1000;}
.sidebar.active{left:0;}
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar a{display:block;color:white;text-decoration:none;padding:12px;border-radius:8px;margin:8px 0;transition:0.3s;}
.sidebar a:hover{background-color:rgba(255,255,255,0.2);}
.close-btn{position:absolute;right:15px;top:15px;font-size:20px;cursor:pointer;}

/* HERO */
.hero {
    background-image:url('images/complex_banner.png');
    height:320px;
    background-size:cover;
    background-position:center;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    text-shadow:2px 2px 6px #000;
    margin-bottom:30px;
    border-radius:0 0 25px 25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.3);
}
.hero h2 {
    font-size:36px;
    text-align:center;
    background:rgba(0,0,0,0.5);
    padding:15px 25px;
    border-radius:12px;
    line-height:1.4;
}

/* CARDS */
.cards-horizontal {
    display:flex;
    gap:20px;
    padding:20px;
    overflow-x:auto;
    scroll-behavior:smooth;
}
.cards-horizontal::-webkit-scrollbar { height:8px; }
.cards-horizontal::-webkit-scrollbar-thumb { background:#ccc;border-radius:4px; }

.res-card {
    background:white;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
    padding:20px;
    flex:0 0 320px;
    min-height:480px;
    display:flex;
    flex-direction:column;
    align-items:center;
}
.res-card h3{color:#0077b6;margin-bottom:10px;text-align:center;}
.res-card img{width:100%;height:150px;object-fit:cover;border-radius:10px;margin-bottom:10px;}
.status{display:inline-block;padding:5px 10px;border-radius:8px;color:white;font-weight:bold;}
.status.Pending{background:orange;}
.status.Confirmed{background:green;}
.status.Cancelled{background:red;}
.btn{display:inline-block;padding:8px 15px;background-color:#00bfff;color:white;border-radius:6px;text-decoration:none;font-weight:bold;transition:0.3s;margin-top:10px;width:90%;text-align:center;}
.btn:hover{background-color:#009acd;transform:scale(1.05);}
.btn.cancel{background:#ff4500;}
.btn.cancel:hover{background:#e63946;}
.gold-card{background:linear-gradient(135deg,#FFD700,#FFFACD);}
.res-table-container{width:100%;overflow-x:auto;overflow-y:auto;max-height:250px;border-radius:10px;}
.res-card table{width:600px;border-collapse:collapse;}
.res-card th,.res-card td{padding:10px;text-align:center;border-bottom:1px solid #ccc;font-size:0.9rem;white-space:nowrap;}
.res-card thead{position:sticky;top:0;background:#1e90ff;color:white;}
.res-card tbody tr:hover{background-color:#f0f8ff;}

/* FOOTER */
footer{background-color:#001f3f;color:white;padding:20px 30px;display:flex;justify-content:space-between;flex-wrap:wrap;align-items:center;}
footer a{color:white;text-decoration:none;margin:0 8px;}
footer a:hover{color:#00bfff;}
</style>
</head>
<body>

<!-- SIDEBAR -->
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
    <img src="images/logo1.png" alt="CourtBook Logo" class="logo" onclick="toggleSidebar()">
    <h1>CourtBook</h1>

    <?php
    // Get user info from database
    $userData = $conn->query("SELECT name, profile_img FROM users WHERE user_id = $user_id")->fetch_assoc();
    $userName = $userData['name'] ?? $_SESSION['name'];
    $profileImg = $userData['profile_img'] ?? "profile_default.png";

    // Path from uploads folder
    $profilePath = "uploads/" . $profileImg;
    if (!file_exists($profilePath)) {
        $profilePath = "uploads/profile_default.png";
    }
    ?>

 <!-- Right-side section -->
<div class="header-right">

    <div class="profile-and-login">
        <div class="profile-section" onclick="toggleProfileMenu()">
            <img src="<?php echo htmlspecialchars($profilePath); ?>" alt="Profile" class="profile-avatar">
            <div class="profile-dropdown" id="profileDropdown">
                <a href="settings.php">⚙️ Settings</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>

        <div class="login-buttons">
            <span>Hello, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

</div>
</header>

<!-- HERO -->
<div class="hero">
    <h2>Book Your Favorite Court Anytime!<br>Futsal 🏐 • Badminton 🏸 • Tennis 🎾 • Basketball 🏀</h2>
</div>

<!-- TOP DASHBOARD CARDS -->
<div class="cards-horizontal" style="justify-content:center; flex-wrap:wrap;">
    
    <!-- Trending Court -->
    <div class="res-card gold-card">
        <h3>Most Trending Court</h3>
        <p><?= $trendingCourt['court_name'] ?> (<?= $trendingCourt['count'] ?> bookings)</p>
        <?php $court_img = "images/".strtolower($trendingCourt['court_name']).".png";
        if(!file_exists($court_img)) $court_img="images/tennis.png"; ?>
        <img src="<?= $court_img ?>" alt="<?= $trendingCourt['court_name'] ?>">
        <a href="courts.php" class="btn">View Courts</a>
    </div>

    <!-- Bookings Past 7 Days -->
    <div class="res-card">
        <h3>Bookings Past 7 Days</h3>
        <canvas id="bookingChart"></canvas>
    </div>

    <!-- Clock + Calendar Card -->
    <div class="res-card">
        <h3>Current Time & Calendar</h3>
        <div style="text-align:center;">
            <h1 id="clock" style="font-size:2rem; color:#0077b6; margin:10px 0;"></h1>
            <button class="btn" onclick="toggleCalendar()">📅 View Calendar</button>
            <div id="calendarContainer" style="display:none; margin-top:15px;"></div>
        </div>
    </div>
</div>


<!-- AVAILABLE SPORTS SECTION -->
<div class="sports-section" style="text-align:center; margin:40px auto 20px;">
    <h2 style="font-size:2rem; color:#003366; margin-bottom:8px;">Available Sports</h2>
    <p style="color:#555; font-size:1rem; margin-bottom:25px;">Choose your favorite sport and book a court today</p>

    <div class="sports-grid" style="
        display:flex;
        flex-wrap:wrap;
        justify-content:center;
        gap:25px;
        padding:10px;
        max-width:1100px;
        margin:auto;
    ">

        <!-- Futsal -->
        <div class="sport-card" style="
            background:linear-gradient(135deg,#00c27a,#00e6ac);
            border-radius:18px;
            width:240px;
            padding:25px 20px;
            color:white;
            text-align:left;
            box-shadow:0 8px 18px rgba(0,0,0,0.15);
            transition:0.3s;
        " onmouseover="this.style.transform='translateY(-6px)';" onmouseout="this.style.transform='translateY(0)';">
            <div style='font-size:40px; margin-bottom:8px;'>⚽</div>
            <h3 style='font-size:20px; font-weight:bold;'>Futsal</h3>
            <p>Fast-paced indoor soccer</p>
            <a href='book.php?court_id=3' style='
                display:inline-block;
                margin-top:15px;
                background:white;
                color:#00a86b;
                padding:8px 14px;
                border-radius:8px;
                font-weight:bold;
                text-decoration:none;
                transition:0.3s;
            '>Get Started →</a>
        </div>

        <!-- Badminton -->
        <div class="sport-card" style="
            background:linear-gradient(135deg,#ffb84d,#ff8c00);
            border-radius:18px;
            width:240px;
            padding:25px 20px;
            color:white;
            text-align:left;
            box-shadow:0 8px 18px rgba(0,0,0,0.15);
            transition:0.3s;
        " onmouseover="this.style.transform='translateY(-6px)';" onmouseout="this.style.transform='translateY(0)';">
            <div style='font-size:40px; margin-bottom:8px;'>🏸</div>
            <h3 style='font-size:20px; font-weight:bold;'>Badminton</h3>
            <p>Precision and agility</p>
            <a href='book.php?court_id=1' style='
                display:inline-block;
                margin-top:15px;
                background:white;
                color:#e67300;
                padding:8px 14px;
                border-radius:8px;
                font-weight:bold;
                text-decoration:none;
                transition:0.3s;
            '>Get Started →</a>
        </div>

        <!-- Tennis -->
        <div class="sport-card" style="
            background:linear-gradient(135deg,#33ccff,#0077b6);
            border-radius:18px;
            width:240px;
            padding:25px 20px;
            color:white;
            text-align:left;
            box-shadow:0 8px 18px rgba(0,0,0,0.15);
            transition:0.3s;
        " onmouseover="this.style.transform='translateY(-6px)';" onmouseout="this.style.transform='translateY(0)';">
            <div style='font-size:40px; margin-bottom:8px;'>🎾</div>
            <h3 style='font-size:20px; font-weight:bold;'>Tennis</h3>
            <p>Skill, power, and accuracy</p>
            <a href='book.php?court_id=2' style='
                display:inline-block;
                margin-top:15px;
                background:white;
                color:#0077b6;
                padding:8px 14px;
                border-radius:8px;
                font-weight:bold;
                text-decoration:none;
                transition:0.3s;
            '>Get Started →</a>
        </div>

        <!-- Basketball -->
        <div class="sport-card" style="
            background:linear-gradient(135deg,#8a2be2,#4b0082);
            border-radius:18px;
            width:240px;
            padding:25px 20px;
            color:white;
            text-align:left;
            box-shadow:0 8px 18px rgba(0,0,0,0.15);
            transition:0.3s;
        " onmouseover="this.style.transform='translateY(-6px)';" onmouseout="this.style.transform='translateY(0)';">
            <div style='font-size:40px; margin-bottom:8px;'>🏀</div>
            <h3 style='font-size:20px; font-weight:bold;'>Basketball</h3>
            <p>Team sport excellence</p>
            <a href='book.php?court_id=4' style='
                display:inline-block;
                margin-top:15px;
                background:white;
                color:#4b0082;
                padding:8px 14px;
                border-radius:8px;
                font-weight:bold;
                text-decoration:none;
                transition:0.3s;
            '>Get Started →</a>
        </div>
    </div>
</div>

<!-- MY RESERVATIONS SECTION -->
<div class="res-card" style="
    max-width:1100px;
    margin:40px auto;
    background:white;
    border-radius:16px;
    box-shadow:0 6px 16px rgba(0,0,0,0.15);
    padding:25px 30px;
    animation:fadeIn 0.8s ease-in-out;
">
    <h3 style="
        color:#0077b6;
        text-align:center;
        font-size:1.5rem;
        margin-bottom:15px;
        text-shadow:1px 1px 3px rgba(0,0,0,0.1);
    ">My Reservations</h3>

    <div class="res-table-container" style="
        overflow-x:auto;
        border-radius:10px;
        background:#f9fcff;
        padding:15px;
    ">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#1e90ff;color:white;">
                    <th style="padding:12px;">Court</th>
                    <th style="padding:12px;">Date</th>
                    <th style="padding:12px;">Time</th>
                    <th style="padding:12px;">Price</th>
                    <th style="padding:12px;">Status</th>
                    <th style="padding:12px;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($reservations): foreach($reservations as $r): ?>
                <tr style="text-align:center;border-bottom:1px solid #ccc;">
                    <td style="padding:10px;"><?= $r['court_name'] ?></td>
                    <td style="padding:10px;"><?= $r['date'] ?></td>
                    <td style="padding:10px;"><?= $r['start_time'].' - '.$r['end_time'] ?></td>
                    <td style="padding:10px;">RM<?= $r['total_price'] ?></td>
                    <td style="padding:10px;">
                        <span class="status <?= $r['status'] ?>" 
                            style="padding:6px 10px;border-radius:8px;color:white;font-weight:bold;">
                            <?= $r['status'] ?>
                        </span>
                    </td>
                    <td style="padding:10px;">
                        <?php if($r['status']=='Pending'): ?>
                            <a class="btn" href="edit_booking.php?id=<?= $r['booking_id'] ?>">Edit</a>
                            <a class="btn cancel" href="cancel_booking.php?id=<?= $r['booking_id'] ?>" onclick="return confirm('Cancel?')">Cancel</a>
                        <?php elseif($r['status']=='Confirmed'): ?>
                            <a class="btn cancel" href="cancel_booking.php?id=<?= $r['booking_id'] ?>" onclick="return confirm('Cancel?')">Cancel</a>
                        <?php else: ?> -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center;padding:15px;">No reservations yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fade-in animation -->
<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ABOUT & SYSTEM FEATURES SECTION -->
<div style="display:flex; flex-wrap:wrap; justify-content:center; gap:25px; margin:40px auto; max-width:1100px; animation:fadeIn 0.8s ease-in-out;">

    <!-- Sports Complex Location -->
    <div style="
        background:white;
        border-radius:16px;
        box-shadow:0 6px 16px rgba(0,0,0,0.15);
        padding:25px;
        flex:1 1 450px;
        min-width:320px;
    ">
        <h3 style="color:#0077b6; text-align:center; margin-bottom:10px;">🏟️ Sports Complex Location</h3>
        <p style="text-align:center; color:#333; font-size:15px;">123 Main Street, Kuala Lumpur, Malaysia</p>
        <p style="text-align:center; color:#555; font-size:14px; margin-bottom:15px;">
            State-of-the-art facilities for all sports lovers! Come experience the best futsal, tennis, badminton, and basketball courts in town.
        </p>
        <img src="images/complex.png" alt="Complex" 
             style="width:100%; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
    </div>

    <!-- System Features -->
    <div style="
        background:white;
        border-radius:16px;
        box-shadow:0 6px 16px rgba(0,0,0,0.15);
        padding:25px;
        flex:1 1 450px;
        min-width:320px;
    ">
        <h3 style="color:#0077b6; text-align:center; margin-bottom:15px;">💻 System Features</h3>
        <div style="display:flex; flex-direction:column; gap:15px;">
            
            <div style="display:flex; align-items:center; gap:12px; background:#e6f7ff; padding:12px 15px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                <span style="font-size:22px;">⏱️</span> 
                <span style="font-size:15px; color:#333;">Real-time court availability</span>
            </div>

            <div style="display:flex; align-items:center; gap:12px; background:#e6f7ff; padding:12px 15px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                <span style="font-size:22px;">👤</span> 
                <span style="font-size:15px; color:#333;">User registration & booking management</span>
            </div>

            <div style="display:flex; align-items:center; gap:12px; background:#e6f7ff; padding:12px 15px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                <span style="font-size:22px;">🖥️</span> 
                <span style="font-size:15px; color:#333;">Admin management dashboard</span>
            </div>

            <div style="display:flex; align-items:center; gap:12px; background:#e6f7ff; padding:12px 15px; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                <span style="font-size:22px;">🔒</span> 
                <span style="font-size:15px; color:#333;">Secure authentication system</span>
            </div>

        </div>
    </div>
</div>

<!-- Reuse existing fade animation -->
<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- FOOTER -->
<footer>
    <div>Follow us:
        <a href="#"><img src="images/instagram.png" width="25"></a>
        <a href="#"><img src="images/facebook.png" width="25"></a>
        <a href="#"><img src="images/twitter.png" width="25"></a>
    </div>
    <div>
        <a href="about.php">About</a> | 
        <a href="#">Support</a> | 
        <a href="contact.php">Contact</a>
    </div>
    <div>© 2025 CourtBook. All rights reserved.</div>
</footer>

<script>
// Sidebar toggle
function toggleSidebar(){ document.getElementById("sidebar").classList.toggle("active"); }

// Profile dropdown
function toggleProfileMenu() {
    const menu = document.getElementById('profileDropdown');
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}
window.addEventListener('click', function(e) {
    const profileSection = document.querySelector('.profile-section');
    const dropdown = document.getElementById('profileDropdown');
    if (!profileSection.contains(e.target)) dropdown.style.display = 'none';
});

// Clock
function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText = now.toLocaleTimeString();
}
setInterval(updateClock, 1000);
updateClock();

// Calendar
function toggleCalendar() {
    const container = document.getElementById('calendarContainer');
    if (container.style.display === "none") {
        const now = new Date();
        const month = now.toLocaleString('default', { month: 'long' });
        const year = now.getFullYear();
        let calendarHTML = `<h4 style="color:#0077b6;">${month} ${year}</h4><table style='width:100%; text-align:center; margin-top:10px;'>`;
        calendarHTML += "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
        const firstDay = new Date(year, now.getMonth(), 1).getDay();
        const daysInMonth = new Date(year, now.getMonth() + 1, 0).getDate();
        let day = 1;
        for (let i = 0; i < 6; i++) {
            calendarHTML += "<tr>";
            for (let j = 0; j < 7; j++) {
                if ((i === 0 && j < firstDay) || day > daysInMonth) {
                    calendarHTML += "<td></td>";
                } else {
                    const isToday = day === now.getDate();
                    calendarHTML += `<td style='padding:6px; ${isToday ? "background:#0077b6;color:white;border-radius:8px;" : ""}'>${day}</td>`;
                    day++;
                }
            }
            calendarHTML += "</tr>";
            if (day > daysInMonth) break;
        }
        calendarHTML += "</table>";
        container.innerHTML = calendarHTML;
        container.style.display = "block";
    } else {
        container.style.display = "none";
    }
}

// Chart
<?php
$labels=[];$data=[];$colors=['#1e90ff','#00bfff','#32cd32','#ffcc00','#ff7f50','#ff69b4','#8a2be2'];
while($row=$bookingChartData->fetch_assoc()){$labels[]=$row['day'];$data[]=$row['count'];}
?>
const ctx=document.getElementById('bookingChart').getContext('2d');
new Chart(ctx,{
    type:'pie',
    data:{labels:<?=json_encode($labels)?>,datasets:[{data:<?=json_encode($data)?>,backgroundColor:<?=json_encode($colors)?>}]},
    options:{plugins:{legend:{position:'bottom'}}}
});
</script>
</body>
</html>
