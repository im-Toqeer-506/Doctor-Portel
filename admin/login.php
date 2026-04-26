<?php
/**
 * Admin Login System
 * Secure authentication with session management
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once('../config/db.php');
require_once('../config/session.php');

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, full_name, status FROM admins WHERE username = ? LIMIT 1");
        
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Check if account is active
                if ($admin['status'] !== 'active') {
                    $error = 'This admin account is inactive.';
                } else {
                    // Verify password using bcrypt
                    if (password_verify($password, $admin['password'])) {
                        // Set session variables
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_name'] = $admin['full_name'];
                        $_SESSION['login_time'] = time();
                        
                        // Log login activity
                        $log_stmt = $conn->prepare(
                            "INSERT INTO admin_login_history (admin_id, ip_address, user_agent) VALUES (?, ?, ?)"
                        );
                        if ($log_stmt) {
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
                            $log_stmt->bind_param('iss', $admin['id'], $ip, $user_agent);
                            $log_stmt->execute();
                            $log_stmt->close();
                        }
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = 'Invalid password.';
                    }
                }
            } else {
                $error = 'Username not found.';
            }
            $stmt->close();
        } else {
            $error = 'Database error. Please try again.';
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
    <title>Admin Login - Doctor Portal</title>
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
        }

        .alert-error {
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

        .btn:active {
            transform: scale(0.98);
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

        .demo-credentials {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-top: 20px;
            font-size: 12px;
            color: #1e40af;
        }

        .demo-credentials strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Doctor Portal</h1>
            <p>Admin Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
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

        <div class="demo-credentials">
            <strong>Demo Credentials:</strong>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
    </div>
</body>
</html>
