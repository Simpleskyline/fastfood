<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/session.php";

session_destroy();

echo json_encode([
    "status" => "success",
    "message" => "Logged out"
]);
?>