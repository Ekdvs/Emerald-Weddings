<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $user_id = $_SESSION['user_id'];
    $package_id = intval($_POST['package_id']);

    // Check if already in cart
    $check = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND package_id = ?");
    $check->bind_param("ii", $user_id, $package_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Optionally increase quantity or just ignore
        // For simplicity, do nothing or notify already added
    } else {
        $insert = $conn->prepare("INSERT INTO cart_items (user_id, package_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $user_id, $package_id);
        $insert->execute();
        $insert->close();
    }
    $check->close();

    header("Location: cart.php");
    exit();
} else {
    header("Location: packages.php");
    exit();
}
