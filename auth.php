<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && $_POST['action'] === "signup") {
        // SIGN UP
        $firstname = $_POST['FirstName'] ?? '';
        $lastname  = $_POST['LastName'] ?? '';
        $email     = $_POST['Email'] ?? '';
        $phone     = $_POST['PhoneNumber'] ?? '';
        $address   = $_POST['address'] ?? '';
        $password  = $_POST['Password'] ?? '';
        $confirmpw = $_POST['ConfirmPassword'] ?? '';

        if ($password !== $confirmpw) {
            echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
            exit();
        }

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Email already registered. Please sign in.'); window.location='auth.php';</script>";
            exit();
        }
        $check->close();

        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
        $sql->bind_param("ssssss", $firstname, $lastname, $email, $phone, $address, $hashedPassword);

        if ($sql->execute()) {
            $_SESSION['client_id'] = $conn->insert_id;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Signup failed. Please try again.'); window.history.back();</script>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === "login") {
        // SIGN IN
        $email = $_POST['Email'] ?? '';
        $password = $_POST['Password'] ?? '';

        $sql = "SELECT id, firstname, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['client_id'] = $user['id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['email'] = $user['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('Incorrect password.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Email not found.'); window.history.back();</script>";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Auth - Login / Signup</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .hidden { display: none; }
    .container { display: flex; justify-content: center; margin-top: 50px; }
    .form-box { border: 1px solid #ccc; padding: 20px; border-radius: 10px; }
    .btn { margin-top: 10px; padding: 10px; }
  </style>
</head>
<body>

  <div class="container">

    <!-- LOGIN FORM -->
    <div id="loginForm" class="form-box">
      <h2>Sign In</h2>
      <form method="post" action="auth.php">
        <input type="hidden" name="action" value="login">
        <input type="email" name="Email" placeholder="Email" required>
        <input type="password" name="Password" placeholder="Password" required>
        <button type="submit" class="btn">SIGN IN</button>
      </form>
      <p>Don’t have an account? <a href="#" onclick="toggleForms()">Sign Up</a></p>
    </div>

    <!-- SIGNUP FORM -->
    <div id="signupForm" class="form-box hidden">
      <h2>Create Account</h2>
      <form method="post" action="auth.php">
        <input type="hidden" name="action" value="signup">
        <input type="text" name="FirstName" placeholder="First Name" required>
        <input type="text" name="LastName" placeholder="Last Name" required>
        <input type="email" name="Email" placeholder="Email" required>
        <input type="tel" name="PhoneNumber" placeholder="Phone Number" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="password" name="Password" placeholder="Password" required>
        <input type="password" name="ConfirmPassword" placeholder="Confirm Password" required>
        <button type="submit" class="btn">SIGN UP</button>
      </form>
      <p>Already have an account? <a href="#" onclick="toggleForms()">Sign In</a></p>
    </div>

  </div>

<script>
  function toggleForms() {
    document.getElementById('loginForm').classList.toggle('hidden');
    document.getElementById('signupForm').classList.toggle('hidden');
  }
</script>

</body>
</html>