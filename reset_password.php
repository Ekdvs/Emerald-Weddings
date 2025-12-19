<?php
session_start();
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$message = '';
$showForm = false;

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || !isset($_SESSION['reset_email'])) {
    header("Location: forgot.php");
    exit();
}

$email = $_SESSION['reset_email'];

// Fetch user_id and username using email
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_id, $username);
$stmt->fetch();
$stmt->close();

$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $message = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $hash, $user_id);

        if ($upd->execute()) {
            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ekdvsampath02@gmail.com';
                $mail->Password = 'ldsq olas mmke vhug';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('ekdvsampath02@gmail.com', 'Emerald Weddings');
                $mail->addAddress($email, $username);
                $mail->isHTML(true);
                $mail->Subject = 'Password Changed Successfully';

                $mail->Body = "
                <div style='font-family: Arial, sans-serif; background-color: #fff0f5; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #ffc1d6;'>
                    <h2 style='color: #c71585;'>Hello, $username</h2>
                    <p style='font-size: 16px;'>Your password has been successfully changed.</p>
                    <p style='font-size: 14px; color: #555;'>If this wasn't you, please contact us immediately at <a href='mailto:info@emeraldweddings.lk'>info@emeraldweddings.lk</a>.</p>
                    <br>
                    <p style='font-size: 16px;'>Best regards,<br><strong>Emerald Weddings Team</strong></p>
                    <hr style='margin-top: 20px; border: 0; border-top: 1px solid #ffc1d6;' />
                    <p style='font-size: 12px; color: #999; text-align: center;'>
                        Emerald Weddings | 123 Wedding Lane, Colombo 05 | +94 76 123 4567<br>
                        © 2025 Emerald Weddings. All rights reserved.
                    </p>
                </div>";

                $mail->send();
            } catch (Exception $e) {
                // Optional: Log or ignore email failure
            }

            unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
            $_SESSION['success'] = "Password reset successful. Please login.";
            header("Location: login.php");
            exit();
        } else {
            $message = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password | Emerald Weddings</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .error { color: red; margin-bottom: 15px; }
    .container { max-width: 500px; margin: 50px auto; padding: 20px; }
    input[type="password"] { width: 100%; padding: 10px; margin-bottom: 10px; }
    button { background-color: #c71585; color: #fff; padding: 10px 20px; border: none; border-radius: 20px; cursor: pointer; }
    button:hover { background-color: #a3146d; }
  </style>
</head>
<body>
<header>
        <div class="header-container">
            <div class="logo">
                <img src="image2.jpg" alt="Emerald Weddings">
            </div>
            <nav>
                <a href="index.html" >Home</a>
                <a href="services.html">Services</a>
                <a href="gallery.html" >Gallery</a>
                <a href="packages.php" >Packages </a>
                <a href="contact.php">Contact</a>
                <a href="about.html">About Us</a>
                <a href="login.php" >Login</a>
                <a href="register.php" class="active">Register</a>
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
    text-shadow: 2px 2px 5px #000;">
    Rest Password</div>

<div class="container">
  <h2>Reset Your Password</h2>

  <?php if ($message): ?>
    <p class="error"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if ($showForm): ?>
  <form method="post" action="">
    <label>New Password</label>
    <input type="password" name="password" id="password" required>
    <label>Confirm Password</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    <label><input type="checkbox" id="showPassword"> Show Passwords</label><br><br>
    <button type="submit">Reset Password</button>
  </form>
  <?php endif; ?>
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

<script>
document.getElementById('showPassword').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('password').type = type;
    document.getElementById('confirm_password').type = type;
});
</script>
</body>
</html>
