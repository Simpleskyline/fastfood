<?php
require_once __DIR__ . '/../middleware/auth_check.php';
require_once __DIR__ . '/../middleware/role_check.php';

requireRole('admin');

echo json_encode([
    "success" => true,
    "stats" => [
        "users" => 120,
        "orders" => 450
    ]
]);