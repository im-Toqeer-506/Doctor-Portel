<?php
/**
 * Doctor Login System
 * Secure authentication for approved doctors only
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once('../config/db.php');
require_once('../config/session.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        // Only allow approved doctors
        $stmt = $conn->prepare(
            "SELECT id, username, password, full_name, status FROM doctors 
             WHERE username = ? AND status = 'approved' LIMIT 1"
        );
        
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $doctor = $result->fetch_assoc();
                
                if (password_verify($password, $doctor['password'])) {
                    // Set session
                    $_SESSION['doctor_id'] = $doctor['id'];
                    $_SESSION['doctor_username'] = $doctor['username'];
                    $_SESSION['doctor_name'] = $doctor['full_name'];
                    $_SESSION['login_time'] = time();
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'Invalid username or your account is not approved yet.';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - Doctor Portal</title>
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
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 28px;
            color: var(--color-primary);
            margin: 0;
        }

        .logo p {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-text);
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(30, 90, 150, 0.1);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            background: var(--color-error-bg);
            color: var(--color-error);
            border: 1px solid #fca5a5;
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
        }

        .btn:hover {
            background: var(--color-primary-hover);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Doctor Portal</h1>
            <p>Doctor Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    autocomplete="username"
                    placeholder="Enter your username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="form-footer">
            Not registered? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
