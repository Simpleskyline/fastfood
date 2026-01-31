<?php
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

echo json_encode([
    "status" => "ok",
    "message" => "Database connected successfully"
]);