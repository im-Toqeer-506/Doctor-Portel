<?php
require_once('../config/config.php');

$error = '';
$success = '';

$role = 'doctor';
$name = '';
$email = '';
$specialty = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'doctor';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $specialty = trim($_POST['specialty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($role !== 'admin' && $role !== 'doctor') {
        $error = 'Please select a valid role.';
    } elseif ($role === 'doctor' && ($specialty === '' || $phone === '')) {
        $error = 'Specialty and phone are required for doctors.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'admin') {
            $check = mysqli_prepare($conn, 'SELECT id FROM admins WHERE email = ?');
            if ($check) {
                mysqli_stmt_bind_param($check, 's', $email);
                mysqli_stmt_execute($check);
                $result = mysqli_stmt_get_result($check);
                $exists = $result && mysqli_fetch_assoc($result);
                mysqli_stmt_close($check);
            } else {
                $exists = false;
            }

            if ($exists) {
                $error = 'An admin with this email already exists.';
            } else {
                $stmt = mysqli_prepare($conn, 'INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashed_password);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Admin signup successful. You can login now.';
                        $name = '';
                        $email = '';
                    } else {
                        $error = 'Database error. Please try again.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error = 'Database error. Please try again.';
                }
            }
        } else {
            $check = mysqli_prepare($conn, 'SELECT id FROM doctors WHERE email = ?');
            if ($check) {
                mysqli_stmt_bind_param($check, 's', $email);
                mysqli_stmt_execute($check);
                $result = mysqli_stmt_get_result($check);
                $exists = $result && mysqli_fetch_assoc($result);
                mysqli_stmt_close($check);
            } else {
                $exists = false;
            }

            if ($exists) {
                $error = 'A doctor with this email already exists.';
            } else {
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
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page narrow">
        <header class="header">
            <h1>Signup</h1>
            <p>Register as admin or doctor.</p>
        </header>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="card" method="POST" action="">
            <label for="role">Register as</label>
            <select id="role" name="role" required>
                <option value="doctor" <?php echo $role === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <div id="doctor-fields">
                <label for="specialty">Specialty</label>
                <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($specialty); ?>">

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit">Signup</button>
        </form>
    </div>
    <script>
        const roleSelect = document.getElementById('role');
        const doctorFields = document.getElementById('doctor-fields');

        function toggleDoctorFields() {
            doctorFields.style.display = roleSelect.value === 'doctor' ? 'block' : 'none';
        }

        roleSelect.addEventListener('change', toggleDoctorFields);
        toggleDoctorFields();
    </script>
</body>
</html>
