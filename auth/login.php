<?php
require_once('../config/config.php');

$error = '';
$email = '';
$role = 'doctor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'doctor';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($role !== 'admin' && $role !== 'doctor') {
        $error = 'Please select a valid role.';
    } else {
        if ($role === 'admin') {
            $stmt = mysqli_prepare($conn, 'SELECT id, name, password FROM admins WHERE email = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $admin = $result ? mysqli_fetch_assoc($result) : null;
                mysqli_stmt_close($stmt);

                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_id'] = (int)$admin['id'];
                    $_SESSION['user_name'] = $admin['name'];
                    header('Location: ../admin/dashboard.php');
                    exit;
                }

                $error = 'Invalid admin credentials.';
            } else {
                $error = 'Database error. Please try again.';
            }
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT id, name, password, status FROM doctors WHERE email = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $doctor = $result ? mysqli_fetch_assoc($result) : null;
                mysqli_stmt_close($stmt);

                if ($doctor && password_verify($password, $doctor['password'])) {
                    if ($doctor['status'] !== 'approved') {
                        $error = 'Your account is pending approval.';
                    } else {
                        $_SESSION['user_role'] = 'doctor';
                        $_SESSION['user_id'] = (int)$doctor['id'];
                        $_SESSION['user_name'] = $doctor['name'];
                        header('Location: ../doctor/dashboard.php');
                        exit;
                    }
                } else {
                    $error = 'Invalid doctor credentials.';
                }
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page narrow">
        <header class="header">
            <h1>Login</h1>
            <p>Login as admin or doctor.</p>
        </header>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="card" method="POST" action="">
            <label for="role">Login as</label>
            <select id="role" name="role" required>
                <option value="doctor" <?php echo $role === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit">Login</button>
        </form>
    </div>
</body>
</html>
