<?php
// db.php - central DB connection (fastfood)
$host = 'localhost';
$user = 'root';
$pass = 'Root@1234'; // set if you have a password
$db   = 'fastfood';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die('DB Connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>