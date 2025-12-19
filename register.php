<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php';

session_start();

$message = '';
$messageClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = 'user';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $message = "All fields are required.";
        $messageClass = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageClass = "error";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $messageClass = "error";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $message = "Password must be at least 8 characters long, include uppercase, lowercase, number and special character.";
        $messageClass = "error";
    } else {
        // Check for duplicate username or email
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $name, $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "Username or email already taken. Please try another.";
            $messageClass = "error";
            $checkStmt->close();
        } else {
            $checkStmt->close();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                // Send welcome email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'ekdvsampath02@gmail.com';
                    $mail->Password   = 'ldsq olas mmke vhug';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('ekdvsampath02@gmail.com', 'Emerald Weddings');
                    $mail->addAddress($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to Emerald Weddings!';
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; background-color: #fff0f5; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #ffc1d6;'>
                        <div style='text-align: center;'>
                            <img src='https://yourdomain.com/logo.png' alt='Emerald Weddings' style='width: 150px; margin-bottom: 20px;'>
                        </div>
                        <h2 style='color: #c71585; text-align: center;'>Welcome to Emerald Weddings, $name! 💍</h2>
                        <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                            Thank you for registering with <strong>Emerald Weddings</strong> — Sri Lanka’s premier wedding planning company.
                            We're truly honored to be a part of your special journey. Whether you're dreaming of a classic ceremony or a luxurious destination wedding,
                            our expert team is here to make every detail magical.
                        </p>
                        <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                            From floral arrangements and décor to venues and entertainment, we ensure that your day is seamless, elegant, and unforgettable.
                        </p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='https://emeraldweddings.lk' target='_blank' style='background-color: #c71585; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>Visit Our Website</a>
                        </div>
                        <p style='font-size: 16px; color: #333; line-height: 1.6; text-align: center;'>
                            Have questions? Reach out to us at <a href='mailto:info@emeraldweddings.lk'>info@emeraldweddings.lk</a><br>
                            or call us at <strong>+94 76 123 4567</strong>
                        </p>
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #ffd1dc;'>
                        <p style='font-size: 14px; color: #555; text-align: center;'>
                            💖 We can’t wait to begin this beautiful journey with you.<br>
                            With warm wishes,<br>
                            <strong style='color: #c71585;'>The Emerald Weddings Team</strong>
                        </p>
                        <p style='font-size: 12px; color: #aaa; text-align: center; margin-top: 40px;'>
                            This is an automated message. Please do not reply directly to this email.
                        </p>
                        <footer style='font-size: 12px; color: #999; text-align: center;'>
        <p>Emerald Weddings</p>
        <p>123 Wedding Lane, Colombo 05, Sri Lanka</p>
        <p>Email: <a href='mailto:info@emeraldweddings.lk' style='color: #c71585; text-decoration: none;'>info@emeraldweddings.lk</a> | Phone: +94 76 123 4567</p>
        <p style='margin-top: 10px;'>© 2025 Emerald Weddings. All rights reserved.</p>
    </footer>
                    </div>
                    
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    // Optional: log email failure
                }

                $_SESSION['success'] = "Registration successful. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed. Try again.";
                $messageClass = "error";
            }

            $stmt->close();
        }
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error {
            background-color: #ffe6e6;
            color: red;
            border: 1px solid #ffb2b2;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #e6ffe6;
            color: green;
            border: 1px solid #b2ffb2;
            padding: 10px;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: #c71585;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #a3146d;
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
    Register</div>

<div class="register-container">
    <h2>Register</h2>

    <?php if ($message): ?>
        <div class="message <?= $messageClass ?>"> <?= $message ?> </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" id="password" required >

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <label>
            <input type="checkbox" id="showPasswordToggle"> Show Passwords
        </label>
        <nav style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></nav>

        <button type="submit">Register</button>
    </form>
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
    const showPasswordToggle = document.getElementById('showPasswordToggle');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    showPasswordToggle.addEventListener('change', function() {
        const type = this.checked ? 'text' : 'password';
        passwordInput.type = type;
        confirmPasswordInput.type = type;
    });
</script>

</body>
</html>
