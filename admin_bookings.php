<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle POST actions: update status, delete, refund
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_id'], $_POST['new_status'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST['new_status'], $_POST['update_id']);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['refund_id'])) {
        // Update booking status to refunded
        $stmt = $conn->prepare("UPDATE bookings SET status = 'refunded' WHERE id = ?");
        $stmt->bind_param("i", $_POST['refund_id']);
        $stmt->execute();
        $stmt->close();
    }
}

// Search filters
$search_user = trim($_GET['u'] ?? '');
$search_pkg = trim($_GET['p'] ?? '');

$sql = "
  SELECT b.id, b.user_id, u.username, u.email, p.id AS pkg_id, p.name AS pkg_name,
         b.quantity, b.total_price, b.booking_date, b.status
  FROM bookings b
  JOIN users u ON u.id = b.user_id
  JOIN packages p ON p.id = b.package_id
  WHERE 1
";
$params = [];
$types = '';
if ($search_user) {
    $sql .= " AND u.username LIKE ?";
    $types .= 's';
    $params[] = "%$search_user%";
}
if ($search_pkg) {
    $sql .= " AND p.name LIKE ?";
    $types .= 's';
    $params[] = "%$search_pkg%";
}
$sql .= " ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Bookings | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body{font-family: Arial, sans-serif;background:#f9f9f9;padding:20px;}
        .container{max-width:1200px;margin:auto;background:#fff;padding:20px;border-radius:8px;}
        table{width:100%;border-collapse:collapse;margin-top:20px;}
        th,td{padding:10px;border:1px solid #ddd;text-align:left;}
        .status-confirmed{color:green;font-weight:bold;}
        .status-pending{color:orange;}
        .status-cancelled{color:red;}
        .status-refunded{color:blue;font-weight:bold;}
        .filter-form input{padding:8px;width:200px;margin-right:10px;}
        .filter-form button{padding:8px 12px;}
        .status-select{width:120px;padding:4px;}
        .action-btn {
            padding: 5px 8px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update {background-color: #007bff; color: white;}
        .btn-delete {background-color: #dc3545; color: white;}
        .btn-refund {background-color: #17a2b8; color: white;}
        form.inline {display:inline;}
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
        <h1>All Bookings Management</h1>
        <form method="get" class="filter-form">
            <input name="u" placeholder="Search by username" value="<?= htmlspecialchars($search_user) ?>">
            <input name="p" placeholder="Search by package" value="<?= htmlspecialchars($search_pkg) ?>">
            <button type="submit">Filter</button>
            <br>
            <nav><a href="admin_bookings.php">Reset</a></nav>
        </form>

        <table>
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Email</th><th>Package</th><th>Qty</th>
                    <th>Total</th><th>Status</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($bk = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $bk['id'] ?></td>
                        <td><?= htmlspecialchars($bk['username']) ?></td>
                        <td><?= htmlspecialchars($bk['email']) ?></td>
                        <td><?= htmlspecialchars($bk['pkg_name']) ?></td>
                        <td><?= $bk['quantity'] ?></td>
                        <td><?= number_format($bk['total_price'],2) ?></td>
                        <td class="status-<?= $bk['status'] ?>"><?= ucfirst($bk['status']) ?></td>
                        <td><?= $bk['booking_date'] ?></td>
                        <td>
                            <!-- Update Status -->
                            <form method="post" class="inline" style="margin-bottom:5px;">
                                <input type="hidden" name="update_id" value="<?= $bk['id'] ?>">
                                <select name="new_status" class="status-select" required>
                                    <option value="pending"     <?= $bk['status']=='pending'?'selected':'' ?>>Pending</option>
                                    <option value="confirmed"   <?= $bk['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                    <option value="cancelled"   <?= $bk['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                    <option value="refunded"    <?= $bk['status']=='refunded'?'selected':'' ?>>Refunded</option>
                                </select>
                                <button type="submit" class="action-btn btn-update" title="Update Status">Update</button>
                            </form>

                            <!-- Refund Button -->
                            <?php if ($bk['status'] !== 'refunded'): ?>
                                <form method="post" class="inline" onsubmit="return confirm('Are you sure to refund this booking?');">
                                    <input type="hidden" name="refund_id" value="<?= $bk['id'] ?>">
                                    <button type="submit" class="action-btn btn-refund" title="Refund Booking">Refund</button>
                                </form>
                            <?php endif; ?>

                            <!-- Delete Button -->
                            <form method="post" class="inline" onsubmit="return confirm('Are you sure to delete this booking? This action cannot be undone.');">
                                <input type="hidden" name="delete_id" value="<?= $bk['id'] ?>">
                                <button type="submit" class="action-btn btn-delete" title="Delete Booking">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; if($result->num_rows === 0): ?>
                    <tr><td colspan="9">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
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
