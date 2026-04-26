<?php
/**
 * Process Admin Actions (Approve/Reject)
 * Handles doctor application approvals and rejections
 */

require_once('../config/db.php');
require_once('../config/session.php');

// Verify admin is logged in
require_admin_login();

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die('CSRF token verification failed. Please try again.');
}

$action = sanitize_input($_POST['action'] ?? '');
$doctor_id = intval($_POST['doctor_id'] ?? 0);

// Validate inputs
if (empty($action) || empty($doctor_id)) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: dashboard.php");
    exit();
}

// Validate doctor exists and is pending
$verify_stmt = $conn->prepare("SELECT id, status FROM doctors WHERE id = ? LIMIT 1");
$verify_stmt->bind_param('i', $doctor_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows !== 1) {
    $_SESSION['error'] = 'Doctor not found.';
    header("Location: dashboard.php");
    exit();
}

$doctor = $verify_result->fetch_assoc();
$verify_stmt->close();

if ($doctor['status'] !== 'pending') {
    $_SESSION['error'] = 'This doctor application has already been processed.';
    header("Location: dashboard.php");
    exit();
}

// Process action
if ($action === 'approve') {
    $update_stmt = $conn->prepare(
        "UPDATE doctors SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?"
    );
    $update_stmt->bind_param('ii', $_SESSION['admin_id'], $doctor_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Doctor application approved successfully.';
    } else {
        $_SESSION['error'] = 'Failed to approve doctor application.';
    }
    $update_stmt->close();

} elseif ($action === 'reject') {
    $rejection_reason = sanitize_input($_POST['rejection_reason'] ?? '');
    
    if (empty($rejection_reason)) {
        $_SESSION['error'] = 'Rejection reason is required.';
        header("Location: dashboard.php");
        exit();
    }
    
    // Limit rejection reason length
    $rejection_reason = substr($rejection_reason, 0, 500);
    
    $update_stmt = $conn->prepare(
        "UPDATE doctors SET status = 'rejected', rejection_reason = ?, approved_by = ? WHERE id = ?"
    );
    $update_stmt->bind_param('sii', $rejection_reason, $_SESSION['admin_id'], $doctor_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = 'Doctor application rejected.';
    } else {
        $_SESSION['error'] = 'Failed to reject doctor application.';
    }
    $update_stmt->close();

} else {
    $_SESSION['error'] = 'Invalid action.';
}

$conn->close();

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>
