<?php
require_once('../config/config.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../auth/login.php');
    exit;
}

$name = $_SESSION['user_name'] ?? 'Doctor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-wrapper">
            <header class="auth-head">
                <h1>Doctor Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($name); ?>.</p>
                <div class="top-actions">
                    <a class="btn ghost" href="../doctors.php">Doctors Listing</a>
                    <a class="btn" href="../auth/logout.php">Logout</a>
                </div>
            </header>

            <div class="card">
                <p>Your account is approved and active.</p>
                <span class="badge approved">Approved</span>
            </div>
        </div>
    </div>
</body>
</html>
