<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $message = "❌ Invalid password!";
        }
    } else {
        $message = "❌ User not found!";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <a href="packages.php">Packages</a>
                <a href="contact.php">Contact</a>
                <a href="about.html">About Us</a>
                <a href="login.php" class="active">Login</a>
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
    text-shadow: 2px 2px 5px #000;">Welcome Back to Emerald Weddings</div>

<div class="container">
    <h2>User Login</h2>

    <?php if ($message): ?>
        <div class="message error"><?= $message ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="username_or_email">Username or Email</label>
        <input type="text" name="username_or_email" id="username_or_email" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label><input type="checkbox" id="showPassword"> Show Password</label>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        <br>
        <nav>
        <a href="forgot.php">Forgot Password?</a><br><br>
        Don’t have an account? <a href="register.php">Register</a></nav>
    </div>
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



<script>
    document.getElementById("showPassword").addEventListener("change", function () {
        const passwordField = document.getElementById("password");
        passwordField.type = this.checked ? "text" : "password";
    });
</script>
</body>
</html>