<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_email = $_POST['Email'] ?? '';
    $input_password = $_POST['Password'] ?? '';

    // Query the same table used in signup
    $sql = "SELECT id AS client_id, firstname, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "<script>alert('Database error: Could not prepare statement.'); window.history.back();</script>";
        exit();
    }

    $stmt->bind_param("s", $input_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($input_password, $user['password'])) {
            $_SESSION['client_id'] = $user['client_id'];
            $_SESSION['Email'] = $user['email'];
            $_SESSION['firstname'] = $user['firstname'];

            header("Location: dashboard.html");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found.'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    header("Location: signin.html");
    exit();
}

$conn->close();
?>
