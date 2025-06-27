<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session at the very beginning

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Use your MySQL password if set
$dbname = "fastfood"; // Ensure this matches your database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect form input. Using empty string fallback for safety.
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';

    // 1. Prepare SQL query to prevent SQL Injection
    // Select all necessary user data including client_id and Password hash
    $sql = "SELECT client_id, Username, Email, Password, FirstName FROM clients WHERE Username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "<script>alert('Database error: Could not prepare statement. Please try again later.'); window.history.back();</script>";
        $conn->close();
        exit();
    }

    // Bind the input username parameter
    $stmt->bind_param("s", $input_username); // 's' for string

    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 2. Verify password against the hashed password from the database
            if (password_verify($input_password, $user['Password'])) {
                // Password is correct, set session variables
                $_SESSION['client_id'] = $user['client_id']; // CRITICAL: Store client_id
                $_SESSION['username'] = $user['Username']; // Store username
                $_SESSION['email'] = $user['Email']; // Store email (useful for profile display)
                $_SESSION['firstname'] = $user['FirstName']; // Store first name

                // Redirect to dashboard.html after successful login
                header("Location: dashboard.html");
                exit(); // Always call exit() after header redirects
            } else {
                // Incorrect password
                echo "<script>alert('Incorrect password.'); window.history.back();</script>";
            }
        } else {
            // Username not found
            echo "<script>alert('Username not found.'); window.history.back();</script>";
        }
    } else {
        // Error executing the query
        echo "<script>alert('Database query failed: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close(); // Close the prepared statement
} else {
    // If accessed directly without POST, redirect or show an error
    // For example, redirect to the sign-in page
    header("Location: signin.html"); // Assuming you have a signin.html
    exit();
}

$conn->close(); // Close the database connection
?>