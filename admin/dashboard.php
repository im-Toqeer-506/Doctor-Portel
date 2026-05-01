<?php
require_once('../config/config.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $doctorId = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;

    if ($action === 'status' && $doctorId > 0) {
        $status = $_POST['status'] ?? '';
        if ($status === 'approved' || $status === 'rejected') {
            $stmt = mysqli_prepare($conn, 'UPDATE doctors SET status = ? WHERE id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $status, $doctorId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header('Location: dashboard.php?msg=status_updated');
                exit;
            }
        }
        $error = 'Unable to update doctor status right now.';
    } elseif ($action === 'delete' && $doctorId > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM doctors WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $doctorId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php?msg=deleted');
            exit;
        }
        $error = 'Unable to delete doctor right now.';
    }
}

$doctors = [];
$result = mysqli_query($conn, 'SELECT id, name, email, phone, specialty, image, status FROM doctors ORDER BY id DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
}

$totalDoctors = count($doctors);
$pendingDoctors = 0;
$approvedDoctors = 0;
$rejectedDoctors = 0;
foreach ($doctors as $doctorRow) {
    if ($doctorRow['status'] === 'approved') {
        $approvedDoctors++;
    } elseif ($doctorRow['status'] === 'rejected') {
        $rejectedDoctors++;
    } else {
        $pendingDoctors++;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') {
        $message = 'Doctor created with pending status.';
    } elseif ($_GET['msg'] === 'status_updated') {
        $message = 'Doctor status updated successfully.';
    } elseif ($_GET['msg'] === 'approved') {
        $message = 'Doctor approved successfully.';
    } elseif ($_GET['msg'] === 'rejected') {
        $message = 'Doctor rejected successfully.';
    } elseif ($_GET['msg'] === 'deleted') {
        $message = 'Doctor deleted successfully.';
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
        <header class="header admin-dashboard-hero">
            <p class="eyebrow">Control Panel</p>
            <h1>Doctors Admin Dashboard</h1>
            <p>Review applications, update statuses, and maintain a trusted doctors directory.</p>
            <div class="hero-actions listing-actions">
                <a class="btn" href="approved.php">Approved Doctors</a>
                <a class="btn" href="../doctors.php">Public Listing</a>
                <a class="btn" href="../auth/logout.php">Logout</a>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <section class="stats-grid dashboard-stats-grid">
            <div class="stat-card stat-doctors-total">
                <h3>Total Doctors</h3>
                <p><?php echo (int)$totalDoctors; ?></p>
            </div>
            <div class="stat-card stat-pending">
                <h3>Pending</h3>
                <p><?php echo (int)$pendingDoctors; ?></p>
            </div>
            <div class="stat-card stat-approved">
                <h3>Approved</h3>
                <p><?php echo (int)$approvedDoctors; ?></p>
            </div>
            <div class="stat-card stat-rejected">
                <h3>Rejected</h3>
                <p><?php echo (int)$rejectedDoctors; ?></p>
            </div>
        </section>

        <section class="card dashboard-table-card">
            <div class="dashboard-table-head">
                <h2>Doctors Management</h2>
                <p>Approve, reject, or remove doctors with one click actions.</p>
            </div>
            <?php if (!empty($doctors)): ?>
                <div class="table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Doctor</th>
                                <th>Contact</th>
                                <th>Specialization</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td>#<?php echo (int)$doctor['id']; ?></td>
                                    <td>
                                        <div class="doctor-row-info">
                                            <strong><?php echo htmlspecialchars($doctor['name']); ?></strong>
                                            <span><?php echo htmlspecialchars($doctor['email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['specialty'] ?: 'General Medicine'); ?></td>
                                    <td><span class="badge <?php echo htmlspecialchars($doctor['status']); ?>"><?php echo htmlspecialchars($doctor['status']); ?></span></td>
                                    <td class="actions-cell">
                                        <form method="POST" action="">
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-xs" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="">
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn danger btn-xs" type="submit">Reject</button>
                                        </form>
                                        <form method="POST" action="" onsubmit="return confirm('Delete this doctor?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
                                            <button class="btn danger ghost-danger btn-xs" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted">No doctors found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
