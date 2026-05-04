<?php
require_once(__DIR__ . '/config/config.php');

$approvedDoctors = [];
$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, phone, specialty, image FROM doctors WHERE status = 'approved' ORDER BY id DESC LIMIT 6"
);
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $approvedDoctors[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="home">
        <?php include_once __DIR__ . '/includes/header.php'; ?>

        <main class="container">
            <section class="hero">
                <div class="hero-copy">
                    <p class="eyebrow">Trusted Care Platform</p>
                    <h1>Modern Doctor Portal for Faster Care and Better Experience.</h1>
                    <p class="subhead">
                        Manage doctor onboarding, approvals, and patient-facing listings in one clean system.
                    </p>
                    <div class="hero-actions">
                        <a class="btn" href="auth/login.php?role=admin">Admin Login</a>
                        <a class="btn ghost" href="auth/login.php?role=doctor">Doctor Login</a>
                        <a class="btn ghost" href="auth/register.php?role=doctor">Doctor Register</a>
                    </div>
                    <div class="hero-mini-points">
                        <span>Easy Signup</span>
                        <span>Approval Based Login</span>
                        <span>Public Doctor Listing</span>
                    </div>
                </div>
                <div class="hero-media">
                    <img
                        src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=900&q=60"
                        alt="Doctor speaking with a patient"
                    >
                    <img
                        src="https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?auto=format&fit=crop&w=900&q=60"
                        alt="Care team reviewing notes"
                    >
                </div>
            </section>

            <section class="home-stats">
                <article class="home-stat-card">
                    <h3>10+</h3>
                    <p>Clinics Joined</p>
                </article>
                <article class="home-stat-card">
                    <h3>250+</h3>
                    <p>Doctors Registered</p>
                </article>
                <article class="home-stat-card">
                    <h3>98%</h3>
                    <p>Approval Success Rate</p>
                </article>
                <article class="home-stat-card">
                    <h3>24/7</h3>
                    <p>Portal Access</p>
                </article>
            </section>

            <section class="highlights">
                <div class="highlight-card">
                    <h3>Faster Approvals</h3>
                    <p>Keep new doctors moving with a clean admin queue.</p>
                </div>
                <div class="highlight-card">
                    <h3>Secure Login</h3>
                    <p>Role-based login for admin and doctors with session support.</p>
                </div>
                <div class="highlight-card">
                    <h3>Simple Dashboard</h3>
                    <p>Approve, reject, and manage doctors in one screen.</p>
                </div>
            </section>

            <section class="steps">
                <h2>How It Works</h2>
                <div class="steps-grid">
                    <div class="step-card">
                        <span>01</span>
                        <h3>Doctor Registers</h3>
                        <p>Doctor creates an account from the register page.</p>
                    </div>
                    <div class="step-card">
                        <span>02</span>
                        <h3>Admin Reviews</h3>
                        <p>Admin approves or rejects doctors from dashboard.</p>
                    </div>
                    <div class="step-card">
                        <span>03</span>
                        <h3>Doctor Gets Access</h3>
                        <p>Approved doctors can log in and access doctor dashboard.</p>
                    </div>
                </div>
            </section>

            <section class="doctor-cards-section">
                <div class="doctor-cards-header">
                    <h2>Our Approved Doctors</h2>
                    <p>Meet verified doctors available in our portal.</p>
                </div>

                <?php if (!empty($approvedDoctors)): ?>
                    <div class="doctor-cards-grid">
                        <?php foreach ($approvedDoctors as $doctor): ?>
                            <article class="doctor-card">
                                <img
                                    src="<?php echo htmlspecialchars(doctor_profile_image_url($doctor['image'] ?? null)); ?>"
                                    alt="<?php echo htmlspecialchars($doctor['name']); ?>"
                                >
                                <div class="doctor-card-body">
                                    <div class="doctor-card-status">
                                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                        <span class="badge approved">Approved</span>
                                    </div>
                                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialty'] ?: 'General'); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No approved doctors to show yet.</p>
                    </div>
                <?php endif; ?>
            </section>

            <?php include_once __DIR__ . '/sections/about.php'; ?>
            <?php include_once __DIR__ . '/sections/testimonial.php'; ?>

            <section class="final-cta">
                <h2>Ready to streamline your doctor onboarding?</h2>
                <p>Start now with a simple and beginner-friendly medical portal.</p>
                <div class="hero-actions">
                    <a class="btn" href="auth/register.php?role=doctor">Get Started</a>
                    <a class="btn ghost" href="doctors.php">View Approved Doctors</a>
                </div>
            </section>
        </main>

        <?php include_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</body>
</html>
