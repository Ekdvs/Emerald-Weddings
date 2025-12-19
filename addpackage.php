<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';
$edit_mode = false;
$edit_package = null;

// DELETE package
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Package deleted successfully.";
        $message_type = 'success';
    } else {
        $message = "Failed to delete package.";
        $message_type = 'error';
    }
    $stmt->close();
}

// Fetch profile picture
$profile_picture = '';
$profile_stmt = $conn->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_stmt->bind_result($profile_picture);
$profile_stmt->fetch();
$profile_stmt->close();

// Handle form (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $upload_dir = "uploads/";
    $image_path = '';
    $id = $_POST['package_id'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = "package_" . time() . "." . $ext;
        $target_file = $upload_dir . $filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            $message = "Failed to upload image.";
            $message_type = 'error';
        }
    }

    if ($name && $description && $price) {
        if ($id) {
            if (!$image_path) {
                $stmt = $conn->prepare("SELECT image FROM packages WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($image_path);
                $stmt->fetch();
                $stmt->close();
            }

            $stmt = $conn->prepare("UPDATE packages SET name=?, description=?, price=?, image=? WHERE id=?");
            $stmt->bind_param("ssdsi", $name, $description, $price, $image_path, $id);
            $stmt->execute();
            $stmt->close();
            $message = "Package updated successfully.";
            $message_type = 'success';
        } else {
            if ($image_path) {
                $stmt = $conn->prepare("INSERT INTO packages (name, description, price, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $name, $description, $price, $image_path);
                $stmt->execute();
                $stmt->close();
                $message = "Package created successfully.";
                $message_type = 'success';
            } else {
                $message = "Please upload an image.";
                $message_type = 'error';
            }
        }
    }
}

// Load for editing
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_package = $result->fetch_assoc();
    $stmt->close();
}

// Search
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM packages WHERE name LIKE ? ORDER BY created_at DESC");
    $like = '%' . $search . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
    $packages = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #0066cc;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-group button:hover {
            background-color: #004999;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .package-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .package-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
        }
        .package-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 5px;
        }
        .package-card h3 {
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            padding: 6px 10px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 8px;
        }
        .btn.edit {
            background-color: #f39c12;
        }
        .btn.delete {
            background-color: #e74c3c;
        }
        .btn.delete:hover {
            background-color: #c0392b;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input {
            padding: 8px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        @media (max-width: 768px) {
            .package-list {
                grid-template-columns: 1fr;
            }
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
            <a href="packages.php">Packages</a>
            <a href="contact.php">Contact</a>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<div class="banner" style="background: url('assets/image/banner.jpg') center/cover no-repeat; height: 70vh; color: white; text-align: center; display: flex; justify-content: center; align-items: center; font-size: 2.5em; font-weight: bold; text-shadow: 2px 2px 4px #000;">
  Packeages Management
</div>

<div class="container">
    

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h2><?= $edit_mode ? 'Edit Package' : 'Create New Package' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="package_id" value="<?= $edit_mode ? htmlspecialchars($edit_package['id']) : '' ?>">
        <div class="form-group">
            <label>Package Name</label>
            <input type="text" name="name" required value="<?= $edit_mode ? htmlspecialchars($edit_package['name']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" required><?= $edit_mode ? htmlspecialchars($edit_package['description']) : '' ?></textarea>
        </div>
        <div class="form-group">
            <label>Price (LKR)</label>
            <input type="number" name="price" step="0.01" required value="<?= $edit_mode ? htmlspecialchars($edit_package['price']) : '' ?>">
        </div>
        <?php if ($edit_mode && $edit_package['image']): ?>
            <div class="form-group">
                <label>Current Image</label><br>
                <img src="<?= htmlspecialchars($edit_package['image']) ?>" width="200" style="border-radius:8px;"><br><br>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label><?= $edit_mode ? 'Replace Image (optional)' : 'Package Image' ?></label>
            <input type="file" name="image" accept="image/*" <?= $edit_mode ? '' : 'required' ?>>
        </div>
        <div class="form-group">
            <button type="submit"><?= $edit_mode ? 'Update Package' : 'Upload Package' ?></button>
        </div>
    </form>

    <h2>Uploaded Packages</h2>

    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search packages..." value="<?= htmlspecialchars($search) ?>">
    </form>

    <div class="package-list">
        <?php foreach ($packages as $pkg): ?>
            <div class="package-card">
                <img src="<?= htmlspecialchars($pkg['image']) ?>" alt="<?= htmlspecialchars($pkg['name']) ?>">
                <h3><?= htmlspecialchars($pkg['name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($pkg['description'])) ?></p>
                <p><strong>Rs. <?= number_format($pkg['price'], 2) ?></strong></p>
                <a href="?edit=<?= $pkg['id'] ?>" class="btn edit">Edit</a>
                <a href="?delete=<?= $pkg['id'] ?>" onclick="return confirm('Are you sure to delete this package?');" class="btn delete">Delete</a>
            </div>
        <?php endforeach; ?>
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
