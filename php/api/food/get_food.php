<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";

$stmt = $pdo->query("SELECT id, name, category, price FROM food_items WHERE active = 1");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

