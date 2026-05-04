<?php $activePage = $activePage ?? ''; ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="Doctor Portel home">
            <span class="brand-mark">+</span>
            <span class="brand-name">Doctor Portel</span>
        </a>
        <nav class="nav">
            <a class="<?php echo $activePage === 'home' ? 'active' : ''; ?>" href="index.php">Home</a>
            <a class="<?php echo $activePage === 'doctors' ? 'active' : ''; ?>" href="doctors.php">Doctors Listing</a>
            <a class="<?php echo $activePage === 'login' ? 'active' : ''; ?>" href="auth/login.php">Login</a>
            <a class="btn" href="auth/register.php">Register</a>
        </nav>
    </div>
</header>
