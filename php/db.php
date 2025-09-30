<?php
//central DB connection
$host = 'localhost';
$user = 'root';
$pass = 'Root@1234'; 
$db   = 'fastfood';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die('DB Connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4'); // important for emojis/utf8 data
// good to use this file via `require 'db.php';` in other scripts
?>
