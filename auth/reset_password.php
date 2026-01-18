<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fastfood";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get username/email from query string
    $user_identifier = $_GET['user'] ?? '';
    $new_password = 'password123'; // Default new password
    
    if (empty($user_identifier)) {
        die("Please provide a username or email in the URL like: reset_password.php?user=username");
    }
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update the password
    $stmt = $conn->prepare("UPDATE clients SET Password = ? WHERE Username = ? OR Email = ?");
    $stmt->bind_param("sss", $hashed_password, $user_identifier, $user_identifier);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Password for '$user_identifier' has been reset to: $new_password<br>";
            echo "<a href='auth.html'>Go to login page</a>";
        } else {
            echo "No user found with identifier: $user_identifier";
        }
    } else {
        echo "Error updating password: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
