<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();

$profile_picture = !empty($profile_picture) ? $profile_picture : 'assets/image/default-user.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard | Emerald Weddings</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f7fb;
      color: #102a43;
    }

    .dashboard {
      display: flex;
      min-height: 80vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: #c71585;
      color: white;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .sidebar .profile {
      text-align: center;
      margin-bottom: 30px;
    }

    .sidebar img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #fff;
      margin-bottom: 10px;
    }

    .sidebar nav a {
      display: block;
      color: white;
      text-decoration: none;
      margin: 12px 0;
      font-weight: bold;
      padding: 10px 15px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .sidebar nav a:hover,
    .sidebar nav a.active {
      background-color: #930057;
    }

    .logout-btn {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 600;
      margin-top: 20px;
      width: 100%;
    }

    .logout-btn:hover {
      background: #c0392b;
    }

    /* Main content */
    .main-content {
      flex: 1;
      padding: 40px;
      background: white;
    }

    .main-content h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #c71585;
    }

    .welcome-msg {
      font-size: 1.2rem;
      margin-bottom: 30px;
      color: #334e68;
    }

    .help-box {
      background: #f8f0f5;
      border-left: 6px solid #c71585;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 40px;
    }

    .help-box ul {
      padding-left: 20px;
      line-height: 1.8;
      color: #333;
    }

    .help-box a {
      color: #c71585;
      font-weight: bold;
      text-decoration: none;
    }

    .dashboard-links a {
      display: inline-block;
      background: #c71585;
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      border-radius: 6px;
      margin: 8px 10px 0 0;
      font-weight: 600;
      transition: background 0.3s;
    }

    .dashboard-links a:hover {
      background: #930057;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .dashboard {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 15px;
      }
      .sidebar nav a {
        margin: 5px;
        font-size: 0.9rem;
        padding: 8px 10px;
      }
      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="image2.jpg" alt="Emerald Weddings">
            </div>
            <nav>
                <a href="index.html">Home</a>
                <a href="services.html">Services</a>
                <a href="gallery.html" class=>Gallery</a>
                <a href="packages.php">Packages</a>
                <a href="contact.php">Contact</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </nav>
        </div>
    </header>

<div style="
    background: linear-gradient(rgba(2, 21, 58, 0.5), rgba(2, 15, 42, 0.5)), url('assets/image/banner.jpg');
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
    padding: 150px 0 50px 0;
    border-radius: 10px;
    width: 100%;
    height: 70vh;
    color: white;
    
    text-align: center;
    font-size: 2.5em;
    font-weight: bold;
    text-shadow: 2px 2px 5px #000;">User Dashboard</div>
<div class="dashboard">
  <aside class="sidebar">
    <div class="profile">
      <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
      <div><?= htmlspecialchars($username) ?></div>
    </div>
    <nav>
      <a href="view_profile.php">View Profile</a>
      <a href="packages.php">Browse Packages</a>
      <a href="cart.php">Cart</a>
      <a href="my_bookings.php">Booking History</a>
    </nav>
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </aside>

  <main class="main-content">
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
    <p class="welcome-msg">Manage your profile, view bookings, and explore wedding packages with ease.</p>

    <div class="help-box">
      <p><strong>Need a little help?</strong></p>
      <ul>
        <li>📅 Check your <strong>Booking History</strong> to track all your orders.</li>
        <li>🧾 Use <strong>Browse Packages</strong> to explore wedding deals.</li>
        <li>🛒 Visit your <strong>Cart</strong> to complete your order.</li>
        <li>👤 Go to <strong>View Profile</strong> to update your personal info or photo.</li>
      </ul>
      <p>If you need assistance, <a href="contact.php">contact our support team</a>.</p>
    </div>

    <div class="dashboard-links">
      <a href="view_profile.php">View Profile</a>
      <a href="packages.php">Browse Packages</a>
      <a href="cart.php">Your Cart</a>
      <a href="my_bookings.php">Booking History</a>
    </div>
  </main>
</div>
<footer>
    <div class="">
        <div class="footer-container">
            <div class="footer-column">
                <h3>Emerald Weddings</h3>
                <p>Premium wedding planning services creating unforgettable celebrations since 2010.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="services.html">Services</a></li>
                    <li><a href="gallery.html">Gallery</a></li>
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Wedding Lane, Colombo 05</p>
                <p><i class="fas fa-phone"></i> +94 76 123 4567</p>
                <p><i class="fas fa-envelope"></i> info@emeraldweddings.lk</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Emerald Weddings. All Rights Reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
