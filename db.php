<?php
$host = "localhost";
$user = "root";     // your DB user
$pass = "";         // your DB password
$db   = "fastfood"; // your DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB Connection failed: " . $conn->connect_error]));
}
?>
