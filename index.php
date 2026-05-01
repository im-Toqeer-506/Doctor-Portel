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
        <header class="site-header">
            <div class="container header-inner">
                <div class="brand">
                    <span class="brand-mark">+</span>
                    <span class="brand-name">Doctor Portel</span>
                </div>
                <nav class="nav">
                    <a href="auth/login.php">Login</a>
                    <a class="btn" href="auth/signup.php">Signup</a>
                </nav>
            </div>
        </header>

        <main class="container">
            <section class="hero">
                <div class="hero-copy">
                    <p class="eyebrow">Care that feels human</p>
                    <h1>Connect doctors, patients, and care teams in one place.</h1>
                    <p class="subhead">
                        Simple approvals, faster onboarding, and a welcoming portal for everyone.
                    </p>
                    <div class="hero-actions">
                        <a class="btn" href="auth/signup.php">Get Started</a>
                        <a class="btn ghost" href="auth/login.php">I Already Have an Account</a>
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

            <section class="highlights">
                <div class="highlight-card">
                    <h3>Faster Approvals</h3>
                    <p>Keep new doctors moving with a clean admin queue.</p>
                </div>
                <div class="highlight-card">
                    <h3>Patient Ready</h3>
                    <p>Give patients a calm, modern first impression.</p>
                </div>
                <div class="highlight-card">
                    <h3>Simple by Design</h3>
                    <p>Minimal steps, easy navigation, zero clutter.</p>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="container footer-inner">
                <p>Doctor Portel - Bringing care together.</p>
                <div class="footer-links">
                    <a href="doctor/register.php">Doctor Register</a>
                    <a href="admin/dashboard.php">Admin Dashboard</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
