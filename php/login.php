<?php
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "fastfood";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$user = $_POST['username'];
$pass = $_POST['password'];

$sql = "SELECT * FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $row = $result->fetch_assoc();

  if (password_verify($pass, $row['password'])) {
    session_start();
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];

    if ($row['role'] === 'admin') {
      header("Location: ../pages/admin_dashboard.html");
    } else {
      header("Location: ../pages/dashboard.html");
    }
    exit();
  } else {
    echo "Invalid password.";
  }
} else {
  echo "User not found.";
}

$stmt->close();
$conn->close();
?>