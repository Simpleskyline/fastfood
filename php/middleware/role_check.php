<?php
/**
 * Role Check Middleware
 * Include this file at the top of admin-only pages
 */

// Include auth check first
require_once __DIR__ . '/auth_check.php';

// Function to require admin role
function requireAdmin() {
    requireAuth();
    
    if (!hasRole('admin')) {
        // If user is logged in but not admin, redirect to client dashboard
        header('Location: ../../frontend/pages/dashboard.html');
        exit;
    }
}

// Function to require client role
function requireClient() {
    requireAuth();
    
    if (!hasRole('client')) {
        // If user is logged in but not client, redirect to admin dashboard
        header('Location: ../../frontend/admin/admin_dashboard.html');
        exit;
    }
}

// If this file is accessed directly via AJAX, return role info
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'role_check.php') {
    header('Content-Type: application/json');
    
    if (isAuthenticated()) {
        $user = getCurrentUser();
        echo json_encode([
            'success' => true,
            'role' => $user['role'],
            'is_admin' => hasRole('admin'),
            'is_client' => hasRole('client')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated'
        ]);
    }
    exit;
}
?>