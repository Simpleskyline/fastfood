<?php
/**
 * Authentication Check Middleware
 * Include this file at the top of protected pages to verify user is logged in
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to get current user data
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'firstName' => $_SESSION['first_name'] ?? null,
        'lastName' => $_SESSION['last_name'] ?? null
    ];
}

// Function to require authentication (redirect if not logged in)
function requireAuth($redirectUrl = '../../auth/auth.html') {
    if (!isAuthenticated()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Function to check if user has specific role
function hasRole($role) {
    if (!isAuthenticated()) {
        return false;
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Function to require specific role
function requireRole($role, $redirectUrl = '../../auth/auth.html') {
    if (!hasRole($role)) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// If this file is accessed directly via AJAX, return JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'auth_check.php') {
    header('Content-Type: application/json');
    
    if (isAuthenticated()) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => getCurrentUser()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'logged_in' => false,
            'message' => 'Not authenticated'
        ]);
    }
    exit;
}
?>