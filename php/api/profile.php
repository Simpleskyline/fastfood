<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "Not authenticated"]);
  exit;
}

echo json_encode([
  "name" => $_SESSION['name'],
  "email" => $_SESSION['email'],
  "phone" => $_SESSION['phone'],
  "location" => $_SESSION['location'],
  "created_at" => date("F Y", strtotime($_SESSION['created_at']))
]);
