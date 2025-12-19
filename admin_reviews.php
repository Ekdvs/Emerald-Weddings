<?php
session_start();
require 'db.php';

// Only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Delete logic (GET method for simplicity — recommend POST in real use)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM package_reviews WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Review deleted successfully.";
    } else {
        $error = "Error deleting review.";
    }
    $stmt->close();
}

// Fetch all reviews with user and package info
$sql = "
    SELECT r.id, r.rating, r.review, r.created_at,
           u.username, p.name AS package_name
    FROM package_reviews r
    JOIN users u ON r.user_id = u.id
    JOIN packages p ON r.package_id = p.id
    ORDER BY r.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Package Reviews</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; font-family: Arial, sans-serif; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        h1 { margin-bottom: 20px; color: #c71585; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background: #f9e5f0; color: #c71585; }
        .stars { color: #f5a623; font-size: 1.1em; }
        .delete-btn {
            background: #e74c3c; color: white;
            padding: 6px 12px; border: none;
            border-radius: 5px; font-size: 0.9em;
            cursor: pointer; text-decoration: none;
        }
        .delete-btn:hover { background: #c0392b; }
        .msg { padding: 10px; background: #dff0d8; color: #3c763d; margin-bottom: 15px; border-radius: 4px; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
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
                <a href="contact.php" class="active">Contact</a>
                <a href="about.html">About Us</a>
                <a href="login.php">Login</a>
                <a href="admin_dashboard.php"> Dashboard</a>
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
    text-shadow: 2px 2px 5px #000;">Package Reviews
</div>
<br>
    <h1>All Package Reviews</h1>

    <?php if (isset($message)) echo "<div class='msg'>{$message}</div>"; ?>
    <?php if (isset($error)) echo "<div class='error'>{$error}</div>"; ?>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Package</th>
                <th>User</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['package_name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td class="stars"><?= str_repeat("★", intval($row['rating'])) . str_repeat("☆", 5 - intval($row['rating'])) ?> (<?= $row['rating'] ?>)</td>
                <td><?= nl2br(htmlspecialchars($row['review'])) ?></td>
                <td><?= date("Y-m-d H:i", strtotime($row['created_at'])) ?></td>
                <td>
                    <a href="?delete_id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No reviews found.</p>
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
</body>
</html>
