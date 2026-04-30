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
    <div class="page narrow">
        <header class="header">
            <h1>Doctor Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($name); ?>.</p>
            <p><a class="btn" href="../auth/logout.php">Logout</a></p>
        </header>

        <div class="card">
            <p>Your account is approved and active.</p>
        </div>
    </div>
</body>
</html>
