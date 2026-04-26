<?php
/**
 * Doctor Registration Form
 * Secure registration with prepared statements
 */

require_once('../config/db.php');
require_once('../config/session.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $specialization = sanitize_input($_POST['specialization'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $qualification = sanitize_input($_POST['qualification'] ?? '');
    $experience = intval($_POST['experience'] ?? 0);
    $clinic_address = sanitize_input($_POST['clinic_address'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'All required fields must be filled.';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM doctors WHERE username = ? OR email = ? LIMIT 1");
        $check_stmt->bind_param('ss', $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Username or email already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert doctor registration (status = pending)
            $insert_stmt = $conn->prepare(
                "INSERT INTO doctors 
                (username, email, password, full_name, specialization, phone, qualification, experience, clinic_address, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            
            $insert_stmt->bind_param(
                'sssssssss',
                $username,
                $email,
                $hashed_password,
                $full_name,
                $specialization,
                $phone,
                $qualification,
                $experience,
                $clinic_address
            );
            
            if ($insert_stmt->execute()) {
                $success = 'Registration successful! Awaiting admin approval.';
                // Clear form fields
                $username = $email = $full_name = $specialization = $phone = $qualification = $clinic_address = '';
                $experience = 0;
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration - Doctor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e5a96;
            --color-primary-hover: #164a7d;
            --color-bg: #f4f7fb;
            --color-surface: #ffffff;
            --color-text: #1a2332;
            --color-error: #dc2626;
            --color-error-bg: #fee2e2;
            --color-success: #0d9488;
            --color-success-bg: #ccfbf1;
            --color-border: #e2e8f0;
            --shadow-lg: 0 12px 40px rgba(15, 23, 42, 0.1);
            --radius-md: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            background: var(--color-bg);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            padding: 40px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h1 {
            font-size: 28px;
            color: var(--color-primary);
            margin: 0 0 10px 0;
        }

        .form-header p {
            color: #999;
            font-size: 14px;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: var(--color-error-bg);
            color: var(--color-error);
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: var(--color-success-bg);
            color: var(--color-success);
            border: 1px solid #99f6e4;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-grid .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-text);
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30, 90, 150, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row.three {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .btn {
            width: 100%;
            padding: 12px 14px;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-top: 10px;
        }

        .btn:hover {
            background: var(--color-primary-hover);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .form-footer a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .required {
            color: var(--color-error);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1>Doctor Registration</h1>
                <p>Register to access Doctor Portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            value="<?php echo htmlspecialchars($full_name); ?>"
                            required
                            placeholder="Dr. John Doe"
                        >
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="specialization" 
                            name="specialization" 
                            value="<?php echo htmlspecialchars($specialization); ?>"
                            required
                            placeholder="Cardiology"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($username); ?>"
                            required
                            placeholder="dr_john_doe"
                        >
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>"
                            required
                            placeholder="doctor@example.com"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="At least 6 characters"
                        >
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required
                            placeholder="Confirm password"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($phone); ?>"
                            placeholder="Your phone number"
                        >
                    </div>
                    <div class="form-group">
                        <label for="experience">Experience (Years)</label>
                        <input 
                            type="number" 
                            id="experience" 
                            name="experience" 
                            value="<?php echo htmlspecialchars($experience); ?>"
                            min="0"
                            placeholder="0"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input 
                        type="text" 
                        id="qualification" 
                        name="qualification" 
                        value="<?php echo htmlspecialchars($qualification); ?>"
                        placeholder="MBBS, MD, etc."
                    >
                </div>

                <div class="form-group">
                    <label for="clinic_address">Clinic Address</label>
                    <textarea 
                        id="clinic_address" 
                        name="clinic_address" 
                        placeholder="Your clinic address..."
                    ><?php echo htmlspecialchars($clinic_address); ?></textarea>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>

            <div class="form-footer">
                Already registered? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>
