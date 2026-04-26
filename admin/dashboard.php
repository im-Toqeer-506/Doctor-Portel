<?php
/**
 * Admin Dashboard
 * Display pending doctors and manage approvals/rejections
 */

require_once('../config/db.php');
require_once('../config/session.php');

// Verify admin is logged in
require_admin_login();

// Get pending doctors count
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM doctors WHERE status = 'pending') as pending_count,
    (SELECT COUNT(*) FROM doctors WHERE status = 'approved') as approved_count,
    (SELECT COUNT(*) FROM doctors WHERE status = 'rejected') as rejected_count,
    (SELECT COUNT(*) FROM doctors) as total_count";

$stats = $conn->query($stats_query)->fetch_assoc();

// Get pending doctors with secure query
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$doctors_query = "SELECT 
    id, 
    full_name, 
    email, 
    specialization, 
    qualification,
    experience,
    phone,
    clinic_address,
    created_at,
    status
FROM doctors 
WHERE status = 'pending'
ORDER BY created_at DESC
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($doctors_query);
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$doctors_result = $stmt->get_result();
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}
$stmt->close();

// Get total pending doctors for pagination
$total_pending = $stats['pending_count'];
$total_pages = ceil($total_pending / $per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Doctor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-bg: #f4f7fb;
            --color-surface: #ffffff;
            --color-primary: #1e5a96;
            --color-primary-hover: #164a7d;
            --color-primary-light: #e8f1fa;
            --color-text: #1a2332;
            --color-text-muted: #5c6b7f;
            --color-border: #e2e8f0;
            --color-success: #0d9488;
            --color-success-bg: #ccfbf1;
            --color-danger: #dc2626;
            --color-danger-bg: #fee2e2;
            --color-warning: #b45309;
            --color-warning-bg: #fef3c7;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 12px 40px rgba(15, 23, 42, 0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --transition: 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            padding: 24px 16px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: var(--shadow-sm);
        }

        .sidebar__brand {
            margin-bottom: 30px;
        }

        .sidebar__logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-primary);
        }

        .sidebar__logo span {
            color: var(--color-success);
        }

        .sidebar__nav {
            list-style: none;
        }

        .sidebar__nav li {
            margin-bottom: 8px;
        }

        .sidebar__nav a {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            color: var(--color-text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all var(--transition);
            font-size: 14px;
        }

        .sidebar__nav a:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
        }

        .sidebar__nav a.is-active {
            background: var(--color-primary-light);
            color: var(--color-primary);
            font-weight: 600;
        }

        .sidebar__icon {
            margin-right: 12px;
            display: inline-block;
            width: 20px;
        }

        .logout-btn {
            margin-top: 30px;
            display: block;
            width: 100%;
            padding: 12px 14px;
            background: var(--color-danger-bg);
            color: var(--color-danger);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            transition: all var(--transition);
        }

        .logout-btn:hover {
            background: var(--color-danger);
            color: white;
        }

        .main {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 30px;
        }

        .topbar {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -30px -30px 30px -30px;
            box-shadow: var(--shadow-sm);
        }

        .topbar__title h1 {
            font-size: 24px;
            margin: 0;
        }

        .topbar__user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
            font-size: 14px;
        }

        .user-info .name {
            font-weight: 600;
            color: var(--color-text);
        }

        .user-info .role {
            color: var(--color-text-muted);
            font-size: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--color-primary);
        }

        .stat-card.warning {
            border-left-color: var(--color-warning);
        }

        .stat-card.success {
            border-left-color: var(--color-success);
        }

        .stat-card.danger {
            border-left-color: var(--color-danger);
        }

        .stat-label {
            font-size: 12px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-text);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--color-text);
        }

        .card {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--color-bg);
            border-bottom: 1px solid var(--color-border);
        }

        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--color-border);
            font-size: 14px;
        }

        tbody tr:hover {
            background: var(--color-bg);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending {
            background: var(--color-warning-bg);
            color: var(--color-warning);
        }

        .badge-approved {
            background: var(--color-success-bg);
            color: var(--color-success);
        }

        .badge-rejected {
            background: var(--color-danger-bg);
            color: var(--color-danger);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: var(--color-success-bg);
            color: var(--color-success);
        }

        .btn-approve:hover {
            background: var(--color-success);
            color: white;
        }

        .btn-reject {
            background: var(--color-danger-bg);
            color: var(--color-danger);
        }

        .btn-reject:hover {
            background: var(--color-danger);
            color: white;
        }

        .btn-view {
            background: var(--color-primary-light);
            color: var(--color-primary);
        }

        .btn-view:hover {
            background: var(--color-primary);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state__icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state__text {
            color: var(--color-text-muted);
            font-size: 16px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            padding: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: 13px;
            color: var(--color-text);
        }

        .pagination a:hover {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }

        .pagination .active {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal__content {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            max-width: 500px;
            width: 100%;
            padding: 30px;
            box-shadow: var(--shadow-lg);
        }

        .modal__header h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        .modal__body {
            margin: 20px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }

        .modal__footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-secondary {
            background: var(--color-bg);
            color: var(--color-text);
        }

        .btn-secondary:hover {
            background: var(--color-border);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .topbar {
                margin: -20px -20px 20px -20px;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar__brand">
                <div class="sidebar__logo">Doctor <span>Portal</span></div>
            </div>
            <ul class="sidebar__nav">
                <li>
                    <a href="dashboard.php" class="is-active">
                        <span class="sidebar__icon">◆</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="doctors.php">
                        <span class="sidebar__icon">▣</span>
                        All Doctors
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <span class="sidebar__icon">⚙</span>
                        Settings
                    </a>
                </li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar__title">
                    <h1>Dashboard</h1>
                </div>
                <div class="topbar__user">
                    <div class="user-info">
                        <div class="name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                        <div class="role">Admin</div>
                    </div>
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card warning">
                    <div class="stat-label">Pending Doctors</div>
                    <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Approved Doctors</div>
                    <div class="stat-value"><?php echo $stats['approved_count']; ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Rejected Applications</div>
                    <div class="stat-value"><?php echo $stats['rejected_count']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Doctors</div>
                    <div class="stat-value"><?php echo $stats['total_count']; ?></div>
                </div>
            </div>

            <!-- Pending Doctors Table -->
            <h2 class="section-title">Pending Doctor Approvals</h2>
            <div class="card">
                <?php if (!empty($doctors)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Experience</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doctor['full_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['experience']); ?> years</td>
                                        <td><?php echo date('M d, Y', strtotime($doctor['created_at'])); ?></td>
                                        <td>
                                            <div class="actions">
                                                <button class="btn btn-view" onclick="viewDetails(<?php echo $doctor['id']; ?>)">View</button>
                                                <button class="btn btn-approve" onclick="approveDoctor(<?php echo $doctor['id']; ?>)">Approve</button>
                                                <button class="btn btn-reject" onclick="openRejectModal(<?php echo $doctor['id']; ?>)">Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1">First</a>
                                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>">Next</a>
                                <a href="?page=<?php echo $total_pages; ?>">Last</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">✓</div>
                        <div class="empty-state__text">No pending doctor applications</div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Reject Reason Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal__content">
            <div class="modal__header">
                <h2>Reject Application</h2>
                <p style="color: #999; font-size: 14px;">Provide a reason for rejection</p>
            </div>
            <form method="POST" action="process_action.php">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="doctor_id" id="rejectDoctorId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                
                <div class="modal__body">
                    <div class="form-group">
                        <label for="rejectReason">Rejection Reason</label>
                        <textarea 
                            id="rejectReason" 
                            name="rejection_reason" 
                            required
                            placeholder="Enter reason for rejection..."
                        ></textarea>
                    </div>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-reject">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveDoctor(doctorId) {
            if (confirm('Are you sure you want to approve this doctor?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_action.php';
                
                form.innerHTML = `
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="doctor_id" value="${doctorId}">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openRejectModal(doctorId) {
            document.getElementById('rejectDoctorId').value = doctorId;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
            document.getElementById('rejectReason').value = '';
        }

        function viewDetails(doctorId) {
            // Placeholder for detailed view modal
            alert('View details for doctor: ' + doctorId);
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
