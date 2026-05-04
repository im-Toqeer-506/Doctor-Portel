<?php
require_once('../config/config.php');

$error = '';
$success = '';

$role = $_GET['role'] ?? 'doctor';
if ($role !== 'admin' && $role !== 'doctor') {
    $role = 'doctor';
}
$name = '';
$email = '';
$phone = '';
$specialty = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'doctor';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $password = $_POST['password'] ?? '';
    $imagePath = '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($role !== 'admin' && $role !== 'doctor') {
        $error = 'Please select a valid role.';
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
                        $phone = '';
                        $specialty = '';
                    } else {
                        $error = 'Database error. Please try again.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error = 'Database error. Please try again.';
                }
            }
        } else {
            if ($phone === '' || $specialty === '') {
                $error = 'Phone and specialization are required for doctor registration.';
            } else {
                if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Image upload failed. Please try again.';
                    } else {
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                        $fileName = $_FILES['image']['name'] ?? '';
                        $tmpFile = $_FILES['image']['tmp_name'] ?? '';
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        if (!in_array($extension, $allowedExtensions, true)) {
                            $error = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
                        } else {
                            $uploadDirectory = ensure_doctor_upload_directory();
                            if ($uploadDirectory === false) {
                                $error = 'Upload folder is missing or not writable. Ensure uploads/doctors exists and allow the web server to write to it (e.g. chmod 775 or chown to the Apache user).';
                            } elseif (!is_uploaded_file($tmpFile)) {
                                $error = 'Invalid upload. Please choose the image again.';
                            } else {
                                $newImageName = 'doctor_' . uniqid() . '.' . $extension;
                                $destination = $uploadDirectory . $newImageName;
                                if (move_uploaded_file($tmpFile, $destination)) {
                                    $imagePath = 'uploads/doctors/' . $newImageName;
                                } else {
                                    $error = 'Unable to save image file. Check permissions on uploads/doctors.';
                                }
                            }
                        }
                    }
                }

                if ($error === '') {
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
                            "INSERT INTO doctors (name, email, phone, specialty, image, password, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')"
                        );

                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $specialty, $imagePath, $hashed_password);
                            if (mysqli_stmt_execute($stmt)) {
                                $success = 'Registration successful. Waiting for approval.';
                                $name = '';
                                $email = '';
                                $phone = '';
                                $specialty = '';
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
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-wrapper">
            <header class="auth-head">
                <h1>Create Your Account</h1>
                <p>Register as admin or doctor.</p>
                <div class="top-actions">
                    <a class="btn ghost" href="../index.php">Home</a>
                    <a class="btn ghost" href="login.php">Login</a>
                </div>
            </header>
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form class="card form-grid" method="POST" action="" enctype="multipart/form-data" id="signup-form">
                <div class="form-group">
                    <label for="role">Register as</label>
                    <select id="role" name="role" required>
                        <option value="doctor" <?php echo $role === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div id="doctor-fields" class="form-grid">
                    <div class="form-group">
                        <label for="phone">Mobile Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>

                    <div class="form-group">
                        <label for="specialty">Specialization</label>
                        <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($specialty); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Profile Picture</label>
                        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                        <p class="field-note">Optional. JPG, JPEG, PNG, or WEBP.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="submit-wrap">
                    <button class="btn block" type="submit" id="signup-submit">Register</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const roleSelect = document.getElementById('role');
        const doctorFields = document.getElementById('doctor-fields');
        const phoneInput = document.getElementById('phone');
        const specialtyInput = document.getElementById('specialty');
        const signupForm = document.getElementById('signup-form');
        const signupSubmit = document.getElementById('signup-submit');

        function toggleDoctorFields() {
            const isDoctor = roleSelect.value === 'doctor';
            doctorFields.style.display = isDoctor ? 'grid' : 'none';
            phoneInput.required = isDoctor;
            specialtyInput.required = isDoctor;
        }

        roleSelect.addEventListener('change', toggleDoctorFields);
        toggleDoctorFields();

        if (signupForm && signupSubmit) {
            signupForm.addEventListener('submit', function () {
                signupSubmit.setAttribute('data-loading', 'true');
                signupSubmit.textContent = 'Creating account...';
            });
        }
    </script>
</body>
</html>
