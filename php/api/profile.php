<?php
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$stmt = $pdo->prepare(
    "SELECT id, FirstName, LastName, Username, Email, Role, created_at
     FROM clients WHERE id = ?"
);

$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "user" => $user
]);
