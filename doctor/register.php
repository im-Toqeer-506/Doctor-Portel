<?php
// Simple doctor registration (no login)
require_once('../config/config.php');

$error = '';
$success = '';

$name = '';
$email = '';
$phone = '';
$specialty = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $password = $_POST['password'] ?? '';
    $imagePath = '';

    if ($name === '' || $email === '' || $phone === '' || $specialty === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
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
                    $uploadDirectory = __DIR__ . '/../uploads/doctors/';
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    $newImageName = 'doctor_' . uniqid() . '.' . $extension;
                    $destination = $uploadDirectory . $newImageName;
                    if (move_uploaded_file($tmpFile, $destination)) {
                        $imagePath = 'uploads/doctors/' . $newImageName;
                    } else {
                        $error = 'Unable to save image file.';
                    }
                }
            }
        }

        if ($error === '') {
            // Hash password for basic security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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

        <form class="card" method="POST" action="" enctype="multipart/form-data">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="phone">Mobile Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

            <label for="specialty">Specialization</label>
            <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($specialty); ?>" required>

            <label for="image">Profile Picture</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit">Register</button>
        </form>
    </div>
</body>
</html>
