<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Fetch profile info
$profile_stmt = $conn->prepare("SELECT birthday, address, phone, gender, profile_picture FROM user_profiles WHERE user_id = ?");
$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_stmt->store_result();
$has_profile = $profile_stmt->num_rows > 0;
$profile_stmt->bind_result($birthday, $address, $phone, $gender, $profile_picture);
$profile_stmt->fetch();
$profile_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $birthday = $_POST['birthday'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $upload_dir = "uploads/";
    $profile_picture_path = $profile_picture;

    if (!empty($_FILES['profile_picture']['name'])) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = "profile_" . $user_id . "_" . time() . "." . $ext;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture_path = $target_file;
        } else {
            $message = "Failed to upload image.";
        }
    }

    if ($has_profile) {
        $update = $conn->prepare("UPDATE user_profiles SET birthday=?, address=?, phone=?, gender=?, profile_picture=? WHERE user_id=?");
        $update->bind_param("sssssi", $birthday, $address, $phone, $gender, $profile_picture_path, $user_id);
        $update->execute();
        $update->close();
        $message = "Profile updated successfully.";
    } else {
        $insert = $conn->prepare("INSERT INTO user_profiles (user_id, birthday, address, phone, gender, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isssss", $user_id, $birthday, $address, $phone, $gender, $profile_picture_path);
        $insert->execute();
        $insert->close();
        $message = "Profile created successfully.";
        $has_profile = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Profile | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
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

<div class="banner" style="background: url('assets/image/banner.jpg') center/cover no-repeat; height: 70vh; color: white; text-align: center; display: flex; justify-content: center; align-items: center; font-size: 2.5em; font-weight: bold; text-shadow: 2px 2px 4px #000;">
  View Profile
</div>

<div class="container">
    <h2>Your Profile</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($username) ?>" disabled>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

        <label>Birthday</label>
        <input type="date" name="birthday" value="<?= htmlspecialchars($birthday ?? '') ?>">

        <label>Address</label>
        <textarea name="address"><?= htmlspecialchars($address ?? '') ?></textarea>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>">

        <label>Gender</label>
        <select name="gender">
            <option value="" disabled>Select Gender</option>
            <option value="Male" <?= ($gender ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($gender ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($gender ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <label>Profile Picture</label>
        <?php if (!empty($profile_picture)): ?>
            <div><img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile" style="max-width: 150px; margin-bottom: 10px;"></div>
        <?php endif; ?>
        <input type="file" name="profile_picture" accept="image/*">

        <button type="submit">Save Profile</button>
    </form>
    
</div>
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
