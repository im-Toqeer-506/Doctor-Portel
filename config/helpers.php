<?php
/**
 * Common Helper Functions
 * Utility functions used across the application
 */

/**
 * Format date for display
 */
function format_date($date_string, $format = 'M d, Y H:i') {
    return date($format, strtotime($date_string));
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'success') {
    $_SESSION[$type] = $message;
    header("Location: $url");
    exit();
}

/**
 * Get base URL
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return "$protocol://$host$path";
}

/**
 * Log activity for audit trail
 */
function log_activity($conn, $user_id, $user_type, $action, $details = '') {
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, user_type, action, details, ip_address) 
         VALUES (?, ?, ?, ?, ?)"
    );
    
    if ($stmt) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param('issss', $user_id, $user_type, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $max_size = 5242880, $allowed_types = ['pdf', 'doc', 'docx']) {
    if (empty($file['name'])) {
        return ['error' => 'No file selected'];
    }
    
    if ($file['size'] > $max_size) {
        return ['error' => 'File size exceeds maximum limit'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['error' => 'Invalid file type'];
    }
    
    return ['success' => true, 'extension' => $ext];
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

?>
