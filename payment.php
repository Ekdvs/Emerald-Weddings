<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$message = '';

$user_stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $card_name = trim($_POST['card_name'] ?? '');

    if (empty($card_number) || !preg_match('/^\d{13,19}$/', $card_number)) {
        $errors[] = "Please enter a valid card number (13-19 digits).";
    }

    if (empty($expiry) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
        $errors[] = "Please enter a valid expiry date (MM/YY).";
    } else {
        list($expMonth, $expYear) = explode('/', $expiry);
        $currentYear = (int)date('y');
        $currentMonth = (int)date('m');
        if ((int)$expYear < $currentYear || ((int)$expYear === $currentYear && (int)$expMonth < $currentMonth)) {
            $errors[] = "The card expiry date is in the past.";
        }
    }

    if (empty($cvv) || !preg_match('/^\d{3,4}$/', $cvv)) {
        $errors[] = "Please enter a valid CVV.";
    }

    if (empty($card_name)) {
        $errors[] = "Cardholder name is required.";
    }

    if (empty($errors)) {
        $cart_packages = [];
        $cart_sql = "
            SELECT ci.package_id, p.name, p.price, ci.quantity 
            FROM cart_items ci 
            JOIN packages p ON ci.package_id = p.id 
            WHERE ci.user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        while ($row = $cart_result->fetch_assoc()) {
            $cart_packages[] = $row;
        }
        $cart_stmt->close();

        if (count($cart_packages) === 0) {
            $errors[] = "Your cart is empty.";
        } else {
            $booking_stmt = $conn->prepare("
                INSERT INTO bookings (user_id, package_id, quantity, total_price, status)
                VALUES (?, ?, ?, ?, 'confirmed')
            ");

            $booking_stmt->bind_param("iiid", $user_id, $package_id, $quantity, $total_price);
            $totalPrice = 0;
            foreach ($cart_packages as $pkg) {
                $package_id = $pkg['package_id'];
                $quantity = $pkg['quantity'];
                $total_price = $pkg['price'] * $quantity;
                $totalPrice += $total_price;
                $booking_stmt->execute();
            }
            $booking_stmt->close();

            $conn->query("DELETE FROM cart_items WHERE user_id = $user_id");

            $packageListHtml = '<ul>';
            foreach ($cart_packages as $pkg) {
                $packageListHtml .= '<li>' . htmlspecialchars($pkg['name']) . ' × ' . $pkg['quantity'] . ' - LKR ' . number_format($pkg['price'] * $pkg['quantity'], 2) . '</li>';
            }
            $packageListHtml .= '</ul>';
            $packageListHtml .= '<p><strong>Total Paid:</strong> LKR ' . number_format($totalPrice, 2) . '</p>';

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ekdvsampath02@gmail.com';
                $mail->Password   = 'ldsqolasmmkevhug'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('ekdvsampath02@gmail.com', 'Emerald Weddings');
                $mail->addAddress($user['email'], $user['username']);
                $mail->isHTML(true);
                $mail->Subject = 'Booking Confirmation - Emerald Weddings';

                $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #fff; border: 1px solid #e2e2e2; border-radius: 8px; padding: 20px; color: #333;'>
                    <h2 style='color: #c71585; text-align: center;'>Booking Confirmed!</h2>
                    <p>Dear <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
                    <p>Thank you for your payment. Your booking has been successfully processed.</p>
                    <p><strong style='font-size: 16px; color: #555;'>Package details:</strong></p>
                    <div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>$packageListHtml</div>
                    <p>We look forward to serving you!</p>
                    <p>Best regards,<br><strong>Emerald Weddings Team</strong></p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;' />
                    <footer style='font-size: 12px; color: #999; text-align: center;'>
                        <p>Emerald Weddings</p>
                        <p>123 Wedding Lane, Colombo 05, Sri Lanka</p>
                        <p>Email: <a href='mailto:info@emeraldweddings.lk' style='color: #c71585;'>info@emeraldweddings.lk</a> | Phone: +94 76 123 4567</p>
                        <p style='margin-top: 10px;'>© 2025 Emerald Weddings. All rights reserved.</p>
                    </footer>
                </div>";

                $mail->send();

                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Payment Success</title>
                <link rel="stylesheet" href="assets/css/style.css">
                <style>.container{max-width:600px;margin:100px auto;text-align:center;padding:0 20px;}.success{font-size:1.5em;color:green;margin-bottom:20px;}</style>
                </head><body><div class="container">
                <div class="success">Thank you! Your payment was successful. A confirmation email has been sent to your inbox.</div>
                <a href="packages.php">Back to Packages</a></div></body></html>';
                exit();
            } catch (Exception $e) {
                $errors[] = "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment | Emerald Weddings</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.container { max-width: 600px; margin: 50px auto; padding: 0 20px; }
form label { display: block; margin-top: 15px; font-weight: bold; }
form input { width: 100%; padding: 8px; margin-top: 5px; }
form button { margin-top: 20px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
form button:hover { background: #218838; }
.error { color: red; margin-top: 10px; }
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
            <a href="packages.php" class="active">Packages</a>
            <a href="contact.php">Contact</a>
            <a href="about.html">About Us</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </div>
</header>

<div style="background: linear-gradient(rgba(2, 21, 58, 0.5), rgba(2, 15, 42, 0.5)), url('assets/image/banner.jpg') center center / cover no-repeat fixed; padding: 150px 0 50px 0; color: white; text-align: center; font-size: 2.5em; font-weight: bold; text-shadow: 2px 2px 5px #000;">
    Payment Details
</div>

<div class="container">
    <h1>Payment Details</h1>
    <?php if (!empty($errors)): ?>
        <div class="error"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post">
        <label>Card Number</label>
        <input type="text" name="card_number" maxlength="19" placeholder="1234 5678 9012 3456" required value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>">
        <label>Expiry Date (MM/YY)</label>
        <input type="text" name="expiry" maxlength="5" placeholder="MM/YY" required value="<?= htmlspecialchars($_POST['expiry'] ?? '') ?>">
        <label>CVV</label>
        <input type="password" name="cvv" maxlength="4" placeholder="123" required>
        <label>Cardholder Name</label>
        <input type="text" name="card_name" placeholder="Name on card" required value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>">
        <button type="submit">Pay Now</button>
    </form>
</div>

<footer>
    <div class="footer-container">
        <div class="footer-column">
            <h3>Emerald Weddings</h3>
            <p>Premium wedding planning services creating unforgettable celebrations since 2010.</p>
        </div>
        <div class="footer-column">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="services.html">Services</a></li>
                <li><a href="gallery.html">Gallery</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Contact Us</h3>
            <p>123 Wedding Lane, Colombo 05</p>
            <p>+94 76 123 4567</p>
            <p>info@emeraldweddings.lk</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Emerald Weddings. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>
