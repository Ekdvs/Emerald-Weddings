<?php
session_start();
require 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Fetch profile picture path from DB (assume user_profiles table with user_id and profile_picture)
$profile_picture = 'assets/images/default-avatar.png';

$stmt = $conn->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pic);
if ($stmt->fetch() && !empty($pic)) {
    $profile_picture = htmlspecialchars($pic);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Emerald Weddings</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f7fb; color: #102a43;
    }
    .dashboard { display: flex; min-height: 100vh; }
    .sidebar {
      width: 300px; background-color: #c71585; color: white;
      padding: 30px 25px; display: flex; flex-direction: column;
      justify-content: space-between; align-items: center;
    }
    .sidebar h2 {
      margin: 15px 0 35px 0; font-size: 2rem;
      font-weight: 700; letter-spacing: 1.5px; text-align: center;
      width: 100%;
    }
    .profile-pic {
      width: 110px; height: 110px; border-radius: 50%;
      object-fit: cover; border: 3px solid white;
      box-shadow: 0 0 12px rgba(255,255,255,0.6);
    }
    .sidebar nav { width: 100%; }
    .sidebar nav a {
      display: block; color: white; text-decoration: none;
      margin-bottom: 22px; font-weight: 600; font-size: 1.15rem;
      padding: 14px 20px; border-radius: 8px;
      transition: background-color 0.3s ease;
    }
    .sidebar nav a:hover,
    .sidebar nav a.active {
      background-color: #930057; box-shadow: 0 0 12px #930057;
    }
    .main-content {
      flex: 1; padding: 50px 60px; background: white;
      box-shadow: inset 0 0 20px #ddd;
    }
    .main-content h1 {
      font-size: 2.8rem; margin-bottom: 10px;
      color: #c71585; letter-spacing: 1.5px;
    }
    .welcome-msg {
      font-size: 1.3rem; margin-bottom: 40px; color: #334e68;
    }
    .quick-actions ul {
      list-style-type: none; padding-left: 0;
    }
    .quick-actions li {
      margin-bottom: 14px; font-size: 1.1rem;
    }
    .quick-actions li a {
      color: #c71585; text-decoration: none; font-weight: 600;
      border-bottom: 2px solid transparent;
      transition: border-color 0.25s ease;
    }
    .quick-actions li a:hover {
      border-bottom: 2px solid #c71585;
    }
    .logout-btn {
      background: #e74c3c; color: white; border: none;
      padding: 14px 30px; border-radius: 30px; cursor: pointer;
      font-weight: 700; font-size: 1.1rem; margin-top: auto;
      transition: background-color 0.3s ease; width: 100%;
    }
    .logout-btn:hover { background: #c0392b; }
    @media (max-width: 768px) {
      .dashboard { flex-direction: column; }
      .sidebar {
        width: 100%; flex-direction: row; justify-content: space-around;
        padding: 15px 10px; align-items: center;
      }
      .sidebar h2, .profile-pic { display: none; }
      .sidebar nav {
        display: flex; gap: 15px; width: auto;
      }
      .sidebar nav a {
        margin: 0; font-size: 1rem; padding: 10px 14px;
      }
      .logout-btn {
        margin-top: 0; padding: 10px 20px;
        border-radius: 20px; font-size: 1rem; width: auto;
      }
      .main-content { padding: 30px 20px; }
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
      <a href="gallery.html">Gallery</a>
      <a href="packages.php">Packages</a>
      <a href="contact.php">Contact</a>
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<div class="banner" style="background: url('assets/image/banner.jpg') center/cover no-repeat; height: 70vh; color: white; text-align: center; display: flex; justify-content: center; align-items: center; font-size: 2.5em; font-weight: bold; text-shadow: 2px 2px 4px #000;">
  Admin Dashboard
</div>

<div class="dashboard">
  <aside class="sidebar">
    <img src="<?= $profile_picture ?>" alt="Profile Picture of <?= $username ?>" class="profile-pic" loading="lazy" />
    <h2>Admin Panel</h2>
    <nav>
      <a href="addpackage.php">Manage Packages</a>
      <a href="admin_bookings.php">View Bookings</a>
      <a href="admin_users.php">User Management</a>
      <a href="admin_reviews.php">View Reviews</a> <!-- ✅ New Link Added -->
      <a href="view_profile.php">Go to Profile</a>
    </nav>
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </aside>

  <main class="main-content">
    <h1>Welcome, <?= $username ?>!</h1>
    <p class="welcome-msg">Use the navigation links on the left to manage your website content efficiently.</p>

    <section class="quick-actions">
      <h2>Quick Actions</h2><br>
      <ul>
        <li><a href="addpackages.php">Add or Edit Wedding Packages</a></li>
        <li><a href="admin_bookings.php">Review and Manage Bookings</a></li>
        <li><a href="admin_users.php">Manage Registered Users</a></li>
        <li><a href="admin_reviews.php">View Package Reviews & Feedback</a></li> <!-- ✅ Optional repetition -->
        <li><a href="view_profile.php">Go to Profile</a></li>
      </ul>
    </section>

    <div class="admin-guide" style="margin-top: 40px; background: #fff5fb; border-left: 5px solid #c71585; padding: 25px; border-radius: 8px;">
      <h2 style="color: #c71585; margin-top: 0;"> <?= $username ?> Admin Guide</h2>
      <p>Welcome to your admin control panel! Here are some tips to help you manage the website efficiently:</p>
      <ul style="line-height: 1.8; padding-left: 20px;">
        <li><strong>Manage Packages:</strong> Add new wedding packages or update existing ones from the <a href="addpackage.php" style="color:#c71585">Manage Packages</a> link.</li>
        <li><strong>Booking Overview:</strong> Check new or pending bookings to ensure all clients receive timely confirmations.</li>
        <li><strong>User Monitoring:</strong> Review user accounts for suspicious activity or profile updates.</li>
        <li><strong>Review Feedback:</strong> View all ratings and comments left by users through the <a href="admin_reviews.php" style="color:#c71585">View Reviews</a> page. This helps you monitor service quality and user satisfaction.</li>
        <li><strong>Profile Management:</strong> Keep your own profile and photo updated for internal recognition.</li>
      </ul>
    </div>
  </main>
</div>

<footer>
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
</footer>

</body>
</html>
