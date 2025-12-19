<?php
session_start();
require 'db.php';

$message = '';

// Check if email is set in session, otherwise redirect back to forgot page
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot.php');
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    if (empty($otp)) {
        $message = "Please enter the OTP sent to your email.";
    } else {
        // Check OTP in DB: match email, otp, not used, and not expired
        $stmt = $conn->prepare("SELECT id, expires_at, used FROM password_otp_requests WHERE email = ? AND otp = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ($row['used']) {
                $message = "This OTP has already been used. Please request a new one.";
            } else {
                $expires_at = strtotime($row['expires_at']);
                if ($expires_at < time()) {
                    $message = "This OTP has expired. Please request a new one.";
                } else {
                    // OTP valid - mark it used
                    $update = $conn->prepare("UPDATE password_otp_requests SET used = 1 WHERE id = ?");
                    $update->bind_param("i", $row['id']);
                    $update->execute();

                    // Mark OTP verified flag for this session
                    $_SESSION['otp_verified'] = true;

                    // Redirect to reset password page
                    header('Location: reset_password.php');
                    $cleanup = $conn->prepare("DELETE FROM password_otp_requests WHERE expires_at < NOW()");
$cleanup->execute();
$cleanup->close();
                    exit;
                }
            }
        } else {
            $message = "Invalid OTP. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Verify OTP | Emerald Weddings</title>
  <link rel="stylesheet" href="assets/css/style.css" />
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
                <a href="gallery.html" class="active">Gallery</a>
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
    text-shadow: 2px 2px 5px #000;">Verify OTP</div>
<div class="container">
  <h2>Verify OTP</h2>
  <p>Please enter the 6-digit OTP sent to your email: <strong><?= htmlspecialchars($email) ?></strong></p>

  <?php if ($message): ?>
    <p class="message error"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="post" action="">
    <label for="otp">OTP</label>
    <input type="text" name="otp" id="otp" maxlength="6" pattern="\d{6}" required placeholder="Enter 6-digit OTP" />
    <button type="submit">Verify OTP</button>
  </form>
<br>
  <nav>
    Didn't receive OTP? <a href="forgot.php">Request again</a>
  </nav>
</div>
<!-- Footer -->
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
