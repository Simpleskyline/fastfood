<?php
if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

function requireRole($role) {
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        echo json_encode(["error" => "Access denied"]);
        exit;
    }
}