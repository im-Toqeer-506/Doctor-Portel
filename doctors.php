<?php
require_once(__DIR__ . '/config/config.php');

$doctors = [];
$stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, specialty, image, status FROM doctors WHERE status = 'approved' ORDER BY id DESC");
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Doctors</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="home">
        <?php $activePage = 'doctors'; ?>
        <?php include_once __DIR__ . '/includes/header.php'; ?>

        <main class="page">
            <header class="header listing-hero">
                <p class="eyebrow">Verified Specialists</p>
                <h1>Find the right doctor for your care</h1>
                <p>Browse approved doctors with trusted profiles and clear details.</p>
                <div class="hero-actions listing-actions">
                    <a class="btn ghost" href="index.php">Back to Home</a>
                    <a class="btn ghost" href="auth/login.php">Doctor Login</a>
                    <a class="btn" href="auth/register.php">Join as Doctor</a>
                </div>
            </header>

            <section class="listing-summary">
                <div class="listing-summary-card">
                    <h3>Total Available Doctors</h3>
                    <p><?php echo (int)count($doctors); ?></p>
                    <span>Profiles approved by admin</span>
                </div>
                <div class="listing-summary-card">
                    <h3>Specialties Covered</h3>
                    <p><?php echo !empty($doctors) ? count(array_unique(array_filter(array_map(static function ($row) { return trim((string)$row['specialty']); }, $doctors)))) : 0; ?></p>
                    <span>General, specialist, and consultant care</span>
                </div>
                <div class="listing-summary-card">
                    <h3>Quick Booking Ready</h3>
                    <p>24/7</p>
                    <span>Easy access to doctor details</span>
                </div>
            </section>

            <div class="doctor-cards-header">
                <h2>Approved Doctors Directory</h2>
                <p>Select a specialist based on medical field and profile details.</p>
            </div>

            <section class="doctor-cards-grid listing-grid">
                <?php if (!empty($doctors)): ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <article class="doctor-card">
                            <img
                                src="<?php echo htmlspecialchars(doctor_profile_image_url($doctor['image'] ?? null)); ?>"
                                alt="<?php echo htmlspecialchars($doctor['name']); ?>"
                            >
                            <div class="doctor-card-body">
                                <div class="doctor-card-head">
                                    <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                    <span class="badge approved"><?php echo htmlspecialchars($doctor['status']); ?></span>
                                </div>
                                <p class="doctor-speciality"><?php echo htmlspecialchars($doctor['specialty'] ?: 'General Medicine'); ?></p>
                                <ul class="doctor-meta-list">
                                    <li><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></li>
                                    <li><strong>Mobile:</strong> <?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></li>
                                </ul>
                                <a class="btn block" href="auth/login.php">Book / Contact</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No approved doctors available yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <?php include_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</body>
</html>
