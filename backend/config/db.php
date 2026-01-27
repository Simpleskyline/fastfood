<?php
$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}
return $conn;
?>