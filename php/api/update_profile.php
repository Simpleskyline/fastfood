<?php
session_start();
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "Unauthorized"]);
  exit;
}

include "../db/connection.php";

$sql = "UPDATE users SET name=?, phone=?, location=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
  "sssi",
  $data['name'],
  $data['phone'],
  $data['location'],
  $_SESSION['user_id']
);
$stmt->execute();

$_SESSION['name'] = $data['name'];
$_SESSION['phone'] = $data['phone'];
$_SESSION['location'] = $data['location'];

echo json_encode(["success" => true]);
