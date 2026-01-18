<?php
$conn = new mysqli("localhost", "root", "", "ronz_pizza_db");
// Check connection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $email    = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password
// Validate inputs
  $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $username, $email, $password);
  if ($stmt->execute()) {
    echo "Registered successfully.";
    header("Location: login.html");
  } else {
    echo "Error: " . $conn->error;
  }
}
?>