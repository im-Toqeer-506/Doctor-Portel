<?php
// Simple doctor registration (no login)
require_once('../config/config.php');

$error = '';
$success = '';

$name = '';
$email = '';
$specialty = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $specialty === '' || $phone === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        // Hash password for basic security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO doctors (name, email, specialty, phone, password, status) VALUES (?, ?, ?, ?, ?, 'pending')"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $specialty, $phone, $hashed_password);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Registration successful. Waiting for approval.';
                $name = '';
                $email = '';
                $specialty = '';
                $phone = '';
            } else {
                $error = 'Database error. Please try again.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page narrow">
        <header class="header">
            <h1>Doctor Registration</h1>
            <p>Fill the form and wait for admin approval.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="card" method="POST" action="">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="specialty">Specialty</label>
            <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($specialty); ?>" required>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit">Register</button>
        </form>
    </div>
</body>
</html>
