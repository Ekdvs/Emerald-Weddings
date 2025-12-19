<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove item from cart (if requested)
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $del = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $remove_id, $user_id);
    $del->execute();
    $del->close();
    header("Location: cart.php");
    exit();
}

// Fetch cart items with package info
$sql = "SELECT ci.id as cart_id, p.* FROM cart_items ci JOIN packages p ON ci.package_id = p.id WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Your Cart | Emerald Weddings</title>
<link rel="stylesheet" href="assets/css/style.css" />
<style>
.container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
.cart-item { display: flex; margin-bottom: 20px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.cart-item img { width: 140px; object-fit: cover; }
.cart-item-details { padding: 15px; flex-grow: 1; }
.cart-item-details h3 { margin: 0 0 10px; }
.cart-item-details p { margin: 4px 0; }
.remove-btn { background: #dc3545; border: none; color: white; padding: 6px 12px; cursor: pointer; border-radius: 4px; }
.remove-btn:hover { background: #b02a37; }
.checkout-btn { background: #007bff; border: none; color: white; padding: 12px 20px; cursor: pointer; border-radius: 6px; font-size: 1.1em; }
.checkout-btn:hover { background: #0056b3; }
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
                <a href="packages.php" class="acitve" >Packages </a>
                <a href="contact.php">Contact</a>
                <a href="about.html">About Us</a>
                <a href="login.php" >Login</a>
                <a href="user_dashboard.php">Dashboard</a>
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
    Cart
</div>

<div class="container">
<h1>Your Cart</h1>

<?php if (count($cart_items) > 0): ?>
    <?php
    $total = 0;
    foreach ($cart_items as $item):
        $total += $item['price'];
    ?>
        <div class="cart-item">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="cart-item-details">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                <p><strong>Price:</strong> LKR <?= number_format($item['price'], 2) ?></p>
                <form method="get" action="cart.php" onsubmit="return confirm('Remove this package from cart?');">
                    <input type="hidden" name="remove" value="<?= $item['cart_id'] ?>">
                    <button type="submit" class="remove-btn">Remove</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <h2>Total: LKR <?= number_format($total, 2) ?></h2>

    <form action="payment.php" method="post">
        <button type="submit" class="checkout-btn">Proceed to Payment</button>
    </form>
<?php else: ?>
    <p>Your cart is empty. <nav><a href="packages.php">Browse Packages</a></nav><br></p>
<?php endif; ?>
</div>
<!-- Footer -->
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
