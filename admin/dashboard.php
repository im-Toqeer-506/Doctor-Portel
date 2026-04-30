<?php
// Simple admin dashboard (no login)
require_once('../config/config.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'approved') {
        $message = 'Doctor Approved';
    } elseif ($_GET['msg'] === 'deleted') {
        $message = 'Doctor Deleted';
    }
}

$doctors = [];
$result = mysqli_query($conn, "SELECT id, name, email, specialty, phone, status FROM doctors WHERE status = 'pending' ORDER BY id DESC");
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page">
        <header class="header">
            <h1>Admin Dashboard</h1>
            <p>Pending doctor approvals</p>
            <p><a class="btn" href="../auth/logout.php">Logout</a></p>
        </header>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if (!empty($doctors)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialty</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialty']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                <td><span class="badge pending">pending</span></td>
                                <td>
                                    <a class="btn" href="approve.php?id=<?php echo (int)$doctor['id']; ?>">Approve</a>
                                    <a class="btn danger" href="delete.php?id=<?php echo (int)$doctor['id']; ?>" onclick="return confirm('Delete this doctor?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="muted">No pending doctors.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
