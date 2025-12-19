<?php
// db.php
$host = "sql203.infinityfree.com"; // XAMPP default host
$user = "if0_39337190";       // default XAMPP username
$password = "lYNYxBeizN5tKq";       // default XAMPP password is empty
$dbname = "if0_39337190_wedding"; // database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
