<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../config/db.php";

echo json_encode([
    "status" => "ok",
    "message" => "Backend and database connected"
]);
?>