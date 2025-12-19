<?php
session_start();
require 'db.php';

// Fetch categories
$cat_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);

// Filters
$category_filter = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$where = " WHERE active = 1 ";
$params = [];
$types = "";

if ($category_filter && is_numeric($category_filter)) {
    $where .= " AND category_id = ? ";
    $params[] = $category_filter;
    $types .= "i";
}
if ($min_price !== '' && is_numeric($min_price)) {
    $where .= " AND price >= ? ";
    $params[] = $min_price;
    $types .= "d";
}
if ($max_price !== '' && is_numeric($max_price)) {
    $where .= " AND price <= ? ";
    $params[] = $max_price;
    $types .= "d";
}

$sql = "SELECT p.*, c.name as category_name, 
        (SELECT AVG(rating) FROM package_reviews r WHERE r.package_id = p.id) as avg_rating,
        (SELECT COUNT(*) FROM package_reviews r WHERE r.package_id = p.id) as review_count
        FROM packages p
        LEFT JOIN categories c ON p.category_id = c.id
        $where ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$packages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Wedding Packages Sri Lanka | Affordable & Luxury Options</title>
<link rel="stylesheet" href="assets/css/packages.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="Discover budget-friendly and luxury wedding packages across Sri Lanka. Transparent pricing, stunning venues, and full-service planning options available." />
<style>
.package-list {
    display: grid;
    gap: 30px;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    margin-top: 30px;
}
.package-card {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 0 10px #eee;
    background: #fff;
    text-align: center;
}
.package-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}
.package-card h3 {
    margin-top: 10px;
    font-size: 1.3em;
    color: #c71585;
}
.package-card p {
    font-size: 0.95em;
    margin: 6px 0;
}
.package-price {
    font-weight: bold;
    font-size: 1.2em;
    color: #000;
}
.stars {
    color: #f5a623;
}
.book-btn {
    background-color: #c71585;
    color: #fff;
    border: none;
    padding: 8px 15px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    display: inline-block;
    margin: 5px;
}
.book-btn:hover {
    background-color: #b01378;
}
.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
}
.filter-form select, .filter-form input {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
.filter-form button, .reset-link {
    padding: 8px 14px;
    background-color: #c71585;
    color: white;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
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
            <a href="packages.php" class="active">Packages</a>
            <a href="contact.php">Contact</a>
            <a href="about.html">About Us</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </div>
</header>

<div style="background: linear-gradient(rgba(2, 21, 58, 0.5), rgba(2, 15, 42, 0.5)), url('assets/image/banner.jpg'); background-position: center center; background-repeat: no-repeat; background-size: cover; background-attachment: fixed; padding: 150px 0 50px 0; border-radius: 10px; width: 100%; height: 70vh; color: white; text-align: center; font-size: 2.5em; font-weight: bold; text-shadow: 2px 2px 5px #000;">
    Wedding Packages
</div>

<div class="container" style="max-width: 1100px; margin: 0 auto 50px; padding: 0 20px;">
    <br>
    <h2>Choose Your Package</h2><br>

    <form method="get" class="filter-form">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="min_price" placeholder="Min Price" value="<?= htmlspecialchars($min_price) ?>" step="0.01" />
        <input type="number" name="max_price" placeholder="Max Price" value="<?= htmlspecialchars($max_price) ?>" step="0.01" />
        <button type="submit">Filter</button>
        <nav><a href="packages.php" class="">Reset</a></nav>
    </form>

    <div class="package-list">
        <?php if (count($packages) > 0): ?>
            <?php foreach ($packages as $pkg): ?>
                <div class="package-card">
                    <a href="package_details.php?id=<?= $pkg['id'] ?>">
                        <img src="<?= htmlspecialchars($pkg['image']) ?>" alt="<?= htmlspecialchars($pkg['name']) ?>" />
                    </a>
                    <h3>
                        <a href="package_details.php?id=<?= $pkg['id'] ?>" style="text-decoration: none; color: #c71585;">
                            <?= htmlspecialchars($pkg['name']) ?>
                        </a>
                    </h3>
                    <p class="category">Category: <?= htmlspecialchars($pkg['category_name'] ?? 'N/A') ?></p>
                    <p class="description"><?= nl2br(htmlspecialchars(substr($pkg['description'], 0, 100))) ?>...</p>
                    <p class="package-price">LKR <?= number_format($pkg['price'], 2) ?></p>
                    <p class="rating">
                        <?php
                            $avg = round(floatval($pkg['avg_rating']), 1);
                            $count = intval($pkg['review_count']);
                            if ($count > 0) {
                                echo "Rating: <span class='stars'>".str_repeat('★', round($avg)).str_repeat('☆', 5 - round($avg))."</span> ($avg / 5, $count reviews)";
                            } else {
                                echo "No reviews yet";
                            }
                        ?>
                    </p>
                    <a href="package_details.php?id=<?= $pkg['id'] ?>" class="book-btn">View Details</a>
                    <form method="post" action="add_to_cart.php" class="book-form">
                        <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                        <button type="submit" class="book-btn">Book Now</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-packages">No packages found matching your criteria.</p>
        <?php endif; ?>
    </div>
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
