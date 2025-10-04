<?php
session_start();

// Block access if not logged in OR not admin
if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../html/auth.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
  <p>This is your admin dashboard.</p>
  <a href="logout.php">Logout</a>
</body>
</html>
