<?php
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Save user
$stmt = $conn->prepare("INSERT INTO clients (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
  // Redirect to profile or login
  header("Location: profile.html?email=" . urlencode($email));
} else {
  echo "Signup failed: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
