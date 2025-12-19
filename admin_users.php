<?php
session_start();
require 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = intval($_POST['delete_user_id']);

    // Prevent admin from deleting themselves
    if ($delete_user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own admin account.";
    } else {
        // Delete user and cascade deletes profiles, bookings, etc.
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_user_id);
        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Error deleting user.";
        }
        $stmt->close();
    }
}

// Fetch all users with their profile pictures
$sql = "
    SELECT u.id, u.username, u.email, u.role, p.profile_picture 
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id
    ORDER BY u.username ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin - User Management | Emerald Weddings</title>
<link rel="stylesheet"href="assets/css/style.css">
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f7fb;
    margin: 0; padding: 20px;
    color: #102a43;
  }
  h1 {
    color: #c71585;
    text-align: center;
    margin-bottom: 25px;
  }
  .container {
    max-width: 1000px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(199, 21, 133, 0.3);
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  th, td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
    vertical-align: middle;
  }
  th {
    background: #c71585;
    color: white;
  }
  tr:nth-child(even) {
    background: #f9e6f4;
  }
  .profile-pic {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #c71585;
  }
  .role-admin {
    font-weight: 700;
    color: #930057;
  }
  .role-user {
    font-weight: 600;
    color: #555;
  }
  .btn-delete {
    background: #e74c3c;
    border: none;
    color: white;
    padding: 7px 15px;
    font-weight: 700;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .btn-delete:hover {
    background: #c0392b;
  }
  .message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
  }
  .success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  /* Responsive */
  @media(max-width: 600px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    th {
      display: none;
    }
    tr {
      margin-bottom: 15px;
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 6px;
      background: #fff;
    }
    td {
      border: none;
      padding-left: 50%;
      position: relative;
      text-align: left;
    }
    td:before {
      content: attr(data-label);
      position: absolute;
      left: 15px;
      top: 12px;
      font-weight: 700;
      color: #c71585;
    }
    .btn-delete {
      width: 100%;
      box-sizing: border-box;
    }
  }
</style>
<script>
  function confirmDelete(username) {
    return confirm("Are you sure you want to delete the user: " + username + " ?");
  }
</script>
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
  User Management
</div>
<div class="container">
    <h1>User Management</h1>

    <?php if (!empty($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Profile</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Profile">
                <?php if (!empty($user['profile_picture'])): ?>
                  <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile picture of <?= htmlspecialchars($user['username']) ?>" class="profile-pic" loading="lazy" />
                <?php else: ?>
                  <img src="assets/images/default-avatar.png" alt="Default avatar" class="profile-pic" loading="lazy" />
                <?php endif; ?>
              </td>
              <td data-label="Username"><?= htmlspecialchars($user['username']) ?></td>
              <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
              <td data-label="Role" class="role-<?= htmlspecialchars($user['role']) ?>"><?= ucfirst($user['role']) ?></td>
              <td data-label="Action">
                <form method="post" onsubmit="return confirmDelete('<?= htmlspecialchars(addslashes($user['username'])) ?>');" style="margin:0;">
                  <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>" />
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
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
