<?php
require_once('../config/config.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$doctors = [];
$result = mysqli_query(
    $conn,
    "SELECT id, name, email, specialty, phone, status FROM doctors WHERE status = 'approved' ORDER BY id DESC"
);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Doctors</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page">
        <header class="header">
            <h1>Approved Doctors</h1>
            <p>Only doctors with approved status.</p>
            <p>
                <a class="btn" href="dashboard.php">Back to Dashboard</a>
                <a class="btn" href="../auth/logout.php">Logout</a>
            </p>
        </header>

        <div class="card">
            <?php if (!empty($doctors)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialty</th>
                            <th>Phone</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?php echo (int)$doctor['id']; ?></td>
                                <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialty']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted">No approved doctors found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
