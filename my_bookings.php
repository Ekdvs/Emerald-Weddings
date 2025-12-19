<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
  SELECT b.id, b.quantity, b.total_price, b.booking_date, b.status, p.name AS package_name
  FROM bookings b
  JOIN packages p ON p.id = b.package_id
  WHERE b.user_id = ?
  ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Bookings | Emerald Weddings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            margin: 0;
            padding: 40px 20px;
            color: #334e68;
        }
        a {
            color: #c71585;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }

        /* Container */
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgb(199 21 133 / 0.15);
            border: 1px solid #e1e8f0;
        }

        h1 {
            font-weight: 700;
            font-size: 2.3rem;
            margin-bottom: 25px;
            color: #102a43;
            text-align: center;
            letter-spacing: 1.2px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        thead tr th {
            background: #c71585;
            color: #fff;
            padding: 12px 15px;
            font-weight: 700;
            text-align: left;
            border-radius: 
            letter-spacing: 0.8px;
        }

        tbody tr {
            background: #f9fafb;
            transition: background-color 0.3s ease;
            box-shadow: 0 1px 3px rgb(199 21 133 / 0.1);
            border-radius: 12px;
        }
        tbody tr:hover {
            background-color: #ffe6f0;
        }
        tbody tr td {
            padding: 15px;
            vertical-align: middle;
            color: #334e68;
            font-weight: 600;
            border-left: 8px solid transparent;
            transition: border-color 0.3s ease;
        }

        /* Rounded corners on first and last cells */
        tbody tr td:first-child {
            border-radius: 12px 0 0 12px;
        }
        tbody tr td:last-child {
            border-radius: 0 12px 12px 0;
        }

        /* Status badges */
        .status-pending {
            color: #ad5700;
            background: #fff4e5;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.5px;
            border: 1.5px solid #ad5700;
        }
        .status-confirmed {
            color: #1a7f37;
            background: #e6f4ea;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.5px;
            border: 1.5px solid #1a7f37;
        }
        .status-cancelled {
            color: #b71c1c;
            background: #fdecea;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.5px;
            border: 1.5px solid #b71c1c;
        }
        .status-refunded {
            color: #0b3954;
            background: #dbe9f4;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.5px;
            border: 1.5px solid #0b3954;
        }

        /* Footer link */
        p {
            margin-top: 30px;
            text-align: center;
        }
        p a {
            font-size: 1.1rem;
            padding: 10px 20px;
            border: 2px solid #c71585;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        p a:hover {
            background: #c71585;
            color: white;
           
        
        }
        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 20px 15px;
            }
            thead tr th, tbody tr td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
            h1 {
                font-size: 1.8rem;
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
                <a href="gallery.html" class="">Gallery</a>
                <a href="packages.php">Packages</a>
                <a href="contact.php">Contact</a>
                <a href="login.php">Login</a>
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
    text-shadow: 2px 2px 5px #000;">User History</div>
    <div class="container">
        <p></p><br>

        <h1>My Booking History</h1>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Package</th><th>Qty</th><th>Total (LKR)</th><th>Status</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows): ?>
                    <?php while ($bk = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $bk['id'] ?></td>
                            <td><?= htmlspecialchars($bk['package_name']) ?></td>
                            <td><?= $bk['quantity'] ?></td>
                            <td><?= number_format($bk['total_price'],2) ?></td>
                            <td class="status-<?= $bk['status'] ?>">
                                <?= ucfirst($bk['status']) ?>
                            </td>
                            <td><?= $bk['booking_date'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 30px 0; font-weight:600; color:#777;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="packages.php">Browse Packages</a></p>
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

</body>
</html>
