# Login and Signup Functionality Guide (Viva Ready)

This guide explains authentication flow in your project in a clear viva format.

It covers:
- Signup (Admin + Doctor)
- Login (Admin + Doctor)
- Role-based routing after login
- Session handling
- Logout flow
- Important validations and security points

---

## 1) Files Involved in Authentication

- `auth/signup.php` -> main registration logic
- `auth/register.php` -> clean route alias for signup page
- `auth/login.php` -> login logic for both roles
- `auth/logout.php` -> clear session and sign out
- `config/config.php` -> starts session + DB setup

---

## 2) Signup Flow (Admin + Doctor)

Signup is implemented in `auth/signup.php`.

### Step A: Read form input and validate

```php
$role = $_POST['role'] ?? 'doctor';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$specialty = trim($_POST['specialty'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    $error = 'Name, email, and password are required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email.';
} elseif ($role !== 'admin' && $role !== 'doctor') {
    $error = 'Please select a valid role.';
}
```

### Step B: Hash password before DB insert

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

### Step C: Admin signup path

```php
$check = mysqli_prepare($conn, 'SELECT id FROM admins WHERE email = ?');
mysqli_stmt_bind_param($check, 's', $email);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);
$exists = $result && mysqli_fetch_assoc($result);
mysqli_stmt_close($check);

if (!$exists) {
    $stmt = mysqli_prepare($conn, 'INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashed_password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
```

### Step D: Doctor signup path (default pending)

```php
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO doctors (name, email, phone, specialty, image, password, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);
mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $specialty, $imagePath, $hashed_password);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
```

### Viva explanation
- Admin and doctor share one signup page.
- Doctor gets `pending` status by default.
- Passwords are never stored in plain text.

---

## 3) `auth/register.php` Route Alias

`auth/register.php` only includes the signup page:

```php
require_once __DIR__ . '/signup.php';
```

### Viva explanation
- This gives a clean URL (`/auth/register.php`) while keeping actual logic in one file.

---

## 4) Login Flow (Admin + Doctor)

Login is implemented in `auth/login.php`.

### Step A: Validate login input

```php
$role = $_POST['role'] ?? 'doctor';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $error = 'Email and password are required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email.';
} elseif ($role !== 'admin' && $role !== 'doctor') {
    $error = 'Please select a valid role.';
}
```

### Step B: Admin login branch

```php
$stmt = mysqli_prepare($conn, 'SELECT id, name, password FROM admins WHERE email = ?');
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
```

### Step C: Doctor login branch (status-controlled)

```php
$stmt = mysqli_prepare($conn, 'SELECT id, name, password, status FROM doctors WHERE email = ?');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doctor = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if ($doctor && password_verify($password, $doctor['password'])) {
    if ($doctor['status'] === 'pending') {
        $error = 'Waiting for approval';
    } elseif ($doctor['status'] === 'rejected') {
        $error = 'Your account has been rejected by admin.';
    } elseif ($doctor['status'] !== 'approved') {
        $error = 'Your account is not active.';
    } else {
        $_SESSION['user_role'] = 'doctor';
        $_SESSION['user_id'] = (int)$doctor['id'];
        $_SESSION['user_name'] = $doctor['name'];
        header('Location: ../doctor/dashboard.php');
        exit;
    }
}
```

### Viva explanation
- Role selected from UI decides which table is checked.
- Admin credentials are checked in `admins`.
- Doctor credentials are checked in `doctors`, but login depends on `status`.

---

## 5) Session Start and Session Usage

Session starts in `config/config.php`:

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

After successful login, identity is stored in session:

```php
$_SESSION['user_role'] = 'admin';   // or 'doctor'
$_SESSION['user_id'] = (int)$admin['id'];
$_SESSION['user_name'] = $admin['name'];
```

### Viva explanation
- Session keeps user logged in across pages.
- Role in session is used for authorization checks.

---

## 6) Logout Flow (`auth/logout.php`)

```php
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
header('Location: login.php');
exit;
```

### Viva explanation
- Clears session array.
- Expires session cookie.
- Destroys session and redirects to login.

---

## 7) Full Login/Signup Lifecycle (One Narrative)

1. User opens register page (`auth/register.php`).
2. Backend validates input and role.
3. Password is hashed and stored.
4. Admin account goes to `admins`; doctor account goes to `doctors` with `pending`.
5. User opens login page (`auth/login.php`).
6. Backend validates credentials using `password_verify`.
7. On success, session role/id/name is set.
8. Admin goes to admin dashboard; approved doctor goes to doctor dashboard.
9. Logout clears everything and redirects to login.

---

## 8) Viva Q&A (Quick)

### Q1: How is password security handled?
- Password is hashed with `password_hash`.
- Verification uses `password_verify`.

### Q2: Why doctor login sometimes fails even with correct password?
- Because status check blocks `pending` and `rejected` doctors.

### Q3: How do you manage both admin and doctor in one login page?
- Using `role` select input and role-based backend branch.

### Q4: How is duplicate account creation prevented?
- Email existence is checked first (`SELECT id ... WHERE email = ?`) before insert.

### Q5: How is login state maintained?
- With PHP session variables (`user_role`, `user_id`, `user_name`).

---

## 9) 40-Second Viva Script

"My login/signup module supports two roles: admin and doctor.  
During signup, input is validated and passwords are hashed before storing in database.  
Doctor records are inserted with `pending` status, so they need admin approval first.  
During login, credentials are verified using `password_verify`, then session variables store identity and role.  
Based on role and status, users are redirected to the correct dashboard.  
Logout fully clears session and cookie to end authentication securely."

