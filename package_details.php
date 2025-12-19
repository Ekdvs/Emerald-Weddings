<?php
session_start();
require 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid package ID.");
}

$package_id = intval($_GET['id']);
$message = '';
$user_id = $_SESSION['user_id'] ?? null;

// Fetch package details
$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name,
    (SELECT AVG(rating) FROM package_reviews WHERE package_id = p.id) AS avg_rating,
    (SELECT COUNT(*) FROM package_reviews WHERE package_id = p.id) AS review_count
    FROM packages p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.active = 1
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();
$stmt->close();

if (!$package) {
    die("Package not found or inactive.");
}

// Check if user has already reviewed
$already_reviewed = false;
if ($user_id) {
    $check_stmt = $conn->prepare("SELECT id FROM package_reviews WHERE package_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $package_id, $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    $already_reviewed = $check_stmt->num_rows > 0;
    $check_stmt->close();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id && !$already_reviewed) {
    $rating = intval($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review'] ?? '');

    if ($rating >= 1 && $rating <= 5) {
        $insert = $conn->prepare("INSERT INTO package_reviews (package_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $package_id, $user_id, $rating, $review_text);
        if ($insert->execute()) {
            $message = "Review submitted successfully.";
            $already_reviewed = true;
        } else {
            $message = "Failed to submit review.";
        }
        $insert->close();

        header("Location: package_details.php?id=$package_id");
        exit();
    } else {
        $message = "Please provide a valid rating between 1 and 5.";
    }
}

// Fetch reviews
$reviews_stmt = $conn->prepare("
    SELECT r.rating, r.review, r.created_at, u.username
    FROM package_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.package_id = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $package_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
$reviews_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($package['name']) ?> | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .container { max-width: 900px; margin: 20px auto; padding: 15px; }
        .package-img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; }
        h1 { margin-bottom: 5px; }
        .category { font-style: italic; color: #666; }
        .price { font-weight: bold; font-size: 1.4em; color: #007bff; margin: 10px 0; }
        .rating { color: #f5a623; margin-bottom: 15px; }
        .description { white-space: pre-wrap; margin-bottom: 20px; }
        .reviews { margin-top: 40px; }
        .review { border-top: 1px solid #ccc; padding: 15px 0; }
        .review:first-child { border-top: none; }
        .review .username { font-weight: bold; }
        .review .date { color: #999; font-size: 0.9em; }
        .review .stars { color: #f5a623; }
        form.review-form { margin-top: 30px; }
        form.review-form textarea { width: 100%; height: 100px; padding: 10px; resize: vertical; }
        form.review-form select, form.review-form button { margin-top: 10px; padding: 8px 15px; }
        form.review-form button { background-color: #28a745; border: none; color: white; cursor: pointer; border-radius: 4px; }
        form.review-form button:hover { background-color: #218838; }
        .message { margin: 10px 0; color: green; }
        .error { color: red; }
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
 Packages Details
</div>

<div class="container">
    <img src="<?= htmlspecialchars($package['image']) ?>" alt="<?= htmlspecialchars($package['name']) ?>" class="package-img" />
    <h1><?= htmlspecialchars($package['name']) ?></h1>
    
    <p class="price">Price: LKR <?= number_format($package['price'], 2) ?></p>

    <p class="rating">
        <?php
        $avg = round(floatval($package['avg_rating']), 1);
        $count = intval($package['review_count']);
        if ($count > 0) {
            echo "Average Rating: $avg / 5 ($count review" . ($count > 1 ? "s" : "") . ")";
        } else {
            echo "No reviews yet";
        }
        ?>
    </p>

    <div class="description"><?= nl2br(htmlspecialchars($package['description'])) ?></div>

    <hr />

    <div class="reviews">
        <h2>Reviews</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="review">
                    <div class="username"><?= htmlspecialchars($rev['username']) ?></div>
                    <div class="stars"><?= str_repeat("★", intval($rev['rating'])) . str_repeat("☆", 5 - intval($rev['rating'])) ?></div>
                    <div class="date"><?= date("F j, Y, g:i a", strtotime($rev['created_at'])) ?></div>
                    <p><?= nl2br(htmlspecialchars($rev['review'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php endif; ?>
    </div>

    <?php if ($user_id): ?>
        <?php if ($already_reviewed): ?>
            <p class="message">You have already reviewed this package.</p>
        <?php else: ?>
            <form class="review-form" method="post">
                <h3>Add Your Review</h3>
                <label for="rating">Rating:</label>
                <select name="rating" id="rating" required>
                    <option value="" disabled selected>Select rating</option>
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>

                <label for="review">Review:</label>
                <textarea name="review" id="review" placeholder="Write your review here..." required></textarea>

                <button type="submit">Submit Review</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p><nav><a href="login.php">Login</a></nav><br> to add a review.</p>
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
