<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle AJAX profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "No data received or invalid JSON."]);
        exit;
    }

    if (empty($data['name']) || empty($data['lastname']) || empty($data['email'])) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET FirstName=?, LastName=?, phonenumber=?, address=? WHERE Email=?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "sssss",
        $data['name'],
        $data['lastname'],
        $data['phone'],
        $data['address'],
        $data['email']
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "No matching user found or no changes made."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Load current user details (GET)
$user = null;
if (isset($_SESSION['client_id'])) {
    $client_id = $_SESSION['client_id'];
    $stmt = $conn->prepare("SELECT FirstName, LastName, Email, phonenumber, Address FROM users WHERE client_id=? LIMIT 1");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile | FastFoodie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:600,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"/> <!-- merged CSS later -->
</head>
<body>
    <div class="profile-container">
        <form id="profileForm" autocomplete="off">
            <label for="name">First Name</label>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['FirstName'] ?? '') ?>">

            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" name="lastname" required value="<?= htmlspecialchars($user['LastName'] ?? '') ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['Email'] ?? '') ?>">

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phonenumber'] ?? '') ?>">

            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['Address'] ?? '') ?>">

            <div class="profile-actions">
                <button type="submit">Update</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('profileForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value,
                lastname: document.getElementById('lastname').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value
            };

            fetch('profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(json => {
                alert(json.message);
                if (json.success) {
                    window.location.href = "dashboard.html";
                }
            })
            .catch(() => {
                alert("An error occurred while updating. Please try again.");
            });
        });
    </script>
</body>
</html>
