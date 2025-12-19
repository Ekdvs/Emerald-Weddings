<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php';

session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $user_name);
            $stmt->fetch();

            // Generate OTP
            $otp = random_int(100000, 999999);
            $expires_at = date('Y-m-d H:i:s', time() + 300); // 5 min

            // Save to DB
            $ins = $conn->prepare("INSERT INTO password_otp_requests (user_id, email, otp, expires_at) VALUES (?, ?, ?, ?)");
            $ins->bind_param("isss", $user_id, $email, $otp, $expires_at);
            $ins->execute();

            // Send Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ekdvsampath02@gmail.com';
                $mail->Password = 'ldsq olas mmke vhug'; // Use App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('ekdvsampath02@gmail.com', 'Emerald Weddings');
                $mail->addAddress($email, $user_name);
                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP';

                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #ffffff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; color: #333;'>
                    <p style='font-size: 16px;'>Hi <strong>" . htmlspecialchars($user_name) . "</strong>,</p>

                    <p style='font-size: 16px;'>
                        Your password reset OTP is: 
                        <strong style='font-size: 20px; color: #c71585;'>$otp</strong>
                    </p>

                    <p style='font-size: 14px; color: #555;'>
                        This OTP is valid for <strong>5 minutes</strong>.
                    </p>

                    <p style='font-size: 14px; color: #555;'>
                        If you did not request this, please ignore this email.
                    </p>

                    <p style='font-size: 16px;'>
                        Regards,<br>
                        <strong>Emerald Weddings Team</strong>
                    </p>

                    <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;' />

                    <footer style='font-size: 12px; color: #999; text-align: center;'>
                        <p>Emerald Weddings</p>
                        <p>123 Wedding Lane, Colombo 05, Sri Lanka</p>
                        <p>Email: <a href='mailto:info@emeraldweddings.lk' style='color: #c71585; text-decoration: none;'>info@emeraldweddings.lk</a> | Phone: +94 76 123 4567</p>
                        <p style='margin-top: 10px;'>© 2025 Emerald Weddings. All rights reserved.</p>
                    </footer>
                </div>
                ";

                $mail->send();
                $_SESSION['reset_email'] = $email;
                header('Location: verify_otp.php');
                exit;

            } catch (Exception $e) {
                $message = "Failed to send email. Error: " . $mail->ErrorInfo;
            }
        } else {
            $message = "If your email is registered, an OTP has been sent.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Forgot Password | Emerald Weddings</title>
  <link rel="stylesheet" href="assets/css/style.css" />
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
                <a href="login.php" class="active" >Login</a>
                <a href="register.php" >Register</a>
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
Forgot Password</div>
<div class="container">
  <h2>Forgot Password</h2>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
  <form method="post" action="">
    <label>Email</label>
    <input type="email" name="email" required />
    <button type="submit">Send OTP</button>
  </form>
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
