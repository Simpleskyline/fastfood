<?php
header('Content-Type: application/json');
require 'db.php';

$result = $conn->query("SELECT id, name, email, created_at FROM clients ORDER BY created_at DESC");

$clients = [];
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}
echo json_encode(["success" => true, "clients" => $clients]);
?>
