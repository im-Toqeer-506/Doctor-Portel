<?php
/**
 * Session Configuration & Security Setup
 * Implements secure session management for admin
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session security configuration
    session_set_cookie_params([
        'lifetime' => 0,           // Session cookie expires when browser closes
        'path' => '/',
        'domain' => '',
        'secure' => false,         // Set to true if using HTTPS
        'httponly' => true,        // Prevent JavaScript access to session cookie
        'samesite' => 'Lax'        // CSRF protection
    ]);
    
    session_start();
}

/**
 * Check if admin is logged in
 * Verify session validity
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect to login if not authenticated
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Sanitize input to prevent XSS
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token if not exists
 */
function get_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

?>
