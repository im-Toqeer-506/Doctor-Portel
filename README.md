# Doctor Portal (Basic PHP CRUD)

Simple beginner-friendly project using:
- Procedural PHP
- MySQL with `mysqli`
- HTML + CSS (no framework)

## Features
- Admin register + login
- Doctor register + login
- Doctor default status is `pending`
- Doctor login allowed only when status is `approved`
- Admin dashboard to:
  - view all doctors
  - approve doctor
  - reject doctor
  - delete doctor
- Public doctors listing page:
  - `doctors.php`
  - shows only approved doctors

## Folder Structure
```text
/Doctor-Portel
├── config/
│   └── config.php
├── admin/
│   ├── dashboard.php
│   ├── approved.php
│   ├── approve.php
│   └── delete.php
├── doctor/
│   ├── dashboard.php
│   └── register.php
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── signup.php
│   └── logout.php
├── includes/
│   ├── header.php
│   └── footer.php
├── doctors.php
├── index.php
└── assets/style.css
```

## Database Setup
1. Open phpMyAdmin.
2. Create database: `doctor_system`
3. Run:

```sql
CREATE DATABASE IF NOT EXISTS doctor_system;
USE doctor_system;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'
);
```

## Configuration
Check database connection in `config/config.php`:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'doctor_system';
```

## Run
1. Start Apache and MySQL.
2. Put project in htdocs.
3. Open:
   - Home: `http://localhost/Doctor-Portel/index.php`
   - Login: `http://localhost/Doctor-Portel/auth/login.php`
   - Register: `http://localhost/Doctor-Portel/auth/register.php`
   - Admin dashboard (after admin login): `http://localhost/Doctor-Portel/admin/dashboard.php`

## Notes
- Passwords are stored using `password_hash()`.
- Logins are checked using `password_verify()`.
- Sessions are used for authentication.
- Queries use prepared statements where user input is involved.

## Public Doctor Listing (How It Works)

This project has two public listing views:

1. `index.php` shows a small preview (latest 6 approved doctors).
2. `doctors.php` shows the full approved doctors directory.

Both pages only show doctors with `status = 'approved'`, so doctors in `pending` or `rejected` are hidden.

### 1) Listing preview on the home page (`index.php`)

The home page runs a SELECT query to fetch only approved doctors and limits it to 6 results:

```php
$approvedDoctors = [];
$stmt = mysqli_prepare(
  $conn,
  "SELECT name, email, phone, specialty, image FROM doctors WHERE status = 'approved' ORDER BY id DESC LIMIT 6"
);
if ($stmt) {
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($result && $row = mysqli_fetch_assoc($result)) {
    $approvedDoctors[] = $row;
  }
  mysqli_stmt_close($stmt);
}
```

Then it loops and renders cards safely using `htmlspecialchars`:

```php
<?php if (!empty($approvedDoctors)): ?>
  <div class="doctor-cards-grid">
    <?php foreach ($approvedDoctors as $doctor): ?>
      <article class="doctor-card">
        <img
          src="<?php echo htmlspecialchars($doctor['image'] !== '' ? $doctor['image'] : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=60'); ?>"
          alt="<?php echo htmlspecialchars($doctor['name']); ?>"
        >
        <div class="doctor-card-body">
          <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
          <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialty'] ?: 'General'); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
          <p><strong>Mobile:</strong> <?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="card">
    <p class="muted">No approved doctors to show yet.</p>
  </div>
<?php endif; ?>
```

### 2) Full listing page (`doctors.php`)

This page loads all approved doctors and displays them in a grid:

```php
$doctors = [];
$stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, specialty, image, status FROM doctors WHERE status = 'approved' ORDER BY id DESC");
if ($stmt) {
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($result && $row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
  }
  mysqli_stmt_close($stmt);
}
```

It then renders each doctor with safe output and default fallbacks:

```php
<?php if (!empty($doctors)): ?>
  <?php foreach ($doctors as $doctor): ?>
    <article class="doctor-card">
      <img
        src="<?php echo htmlspecialchars($doctor['image'] !== '' ? $doctor['image'] : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=60'); ?>"
        alt="<?php echo htmlspecialchars($doctor['name']); ?>"
      >
      <div class="doctor-card-body">
        <div class="doctor-card-head">
          <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
          <span class="badge approved"><?php echo htmlspecialchars($doctor['status']); ?></span>
        </div>
        <p class="doctor-speciality"><?php echo htmlspecialchars($doctor['specialty'] ?: 'General Medicine'); ?></p>
        <ul class="doctor-meta-list">
          <li><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></li>
          <li><strong>Mobile:</strong> <?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></li>
        </ul>
        <a class="btn block" href="auth/login.php">Book / Contact</a>
      </div>
    </article>
  <?php endforeach; ?>
<?php else: ?>
  <div class="card">
    <p class="muted">No approved doctors available yet.</p>
  </div>
<?php endif; ?>
```

### Why this is safe and clean

- Only approved doctors appear because of the `WHERE status = 'approved'` filter.
- Output is escaped with `htmlspecialchars(...)` to prevent HTML injection.
- Fallback values are shown when a specialty, phone, or image is missing.

---

# Beginner-Friendly Guide: How This PHP CRUD App Works

This section explains the full flow of a PHP CRUD app from browser click to database and back, with simple code snippets.

## 1) Big Picture: Project Flow

When you click a link or submit a form in the browser:

1. The browser sends an HTTP request to Apache (part of XAMPP).
2. Apache sees the request targets a `.php` file and hands it to the PHP engine.
3. PHP runs the file on the server. If it needs data, it talks to MySQL.
4. PHP generates HTML as output and sends it back to the browser.
5. The browser renders the HTML/CSS for you.

### Where execution starts

- For a page: the entry is the requested `.php` file (example: `index.php`).
- For a form submit: the entry is the form `action` (example: `auth/register.php`).

### Simple request flow diagram

```
Browser
  |  (HTTP request)
  v
Apache (XAMPP)
  |  (runs PHP)
  v
PHP file (logic)
  |  (DB query)
  v
MySQL (data)
  |  (results)
  v
PHP file (HTML output)
  |  (HTTP response)
  v
Browser (renders page)
```

---

## 2) Database (MySQL)

### Create DB + tables (example)

```sql
CREATE DATABASE IF NOT EXISTS doctor_system;
USE doctor_system;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'
);
```

### How PHP connects

In `config/config.php` you store connection settings:

```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'doctor_system';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
```

Explanation (line by line):

- `$host`, `$user`, `$pass`, `$dbname`: DB credentials
- `mysqli_connect(...)`: opens connection to MySQL
- `if (!$conn)`: stop if connection fails

---

## 3) CRUD Operations (Core Concept)

Below are simple, clean snippets for each CRUD operation.

### Create (Insert)

Form (HTML):

```html
<form method="POST" action="doctor/register.php">
  <input type="text" name="name" required>
  <input type="email" name="email" required>
  <input type="password" name="password" required>
  <button type="submit">Register</button>
</form>
```

PHP handler (simplified):

```php
<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($conn, "INSERT INTO doctors (name, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $password);
    mysqli_stmt_execute($stmt);

    header('Location: /Doctor-Portel/auth/login.php');
    exit;
}
```

Explanation:

- `$_SERVER['REQUEST_METHOD']`: ensure this is a POST submit
- `trim(...)`: remove extra spaces
- `password_hash(...)`: secure password
- `mysqli_prepare(...)`: prevents SQL injection
- `header(...)`: redirect after success

### Read (Select)

Fetch approved doctors and display:

```php
<?php
require 'config/config.php';

$sql = "SELECT id, name, email, status FROM doctors WHERE status = 'approved'";
$result = mysqli_query($conn, $sql);
?>

<ul>
  <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <li><?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['email']); ?>)</li>
  <?php endwhile; ?>
</ul>
```

Explanation:

- `mysqli_query(...)`: run SELECT query
- `mysqli_fetch_assoc(...)`: loop rows one by one
- `htmlspecialchars(...)`: prevent HTML injection

### Update (Edit)

Example: Admin approves a doctor.

```php
<?php
require '../config/config.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = mysqli_prepare($conn, "UPDATE doctors SET status = 'approved' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    header('Location: /Doctor-Portel/admin/dashboard.php');
    exit;
}
```

Explanation:

- `$_GET['id']`: record ID from URL
- `(int) $_GET['id']`: basic input safety
- `UPDATE`: changes existing row

### Delete (Remove)

```php
<?php
require '../config/config.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM doctors WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    header('Location: /Doctor-Portel/admin/dashboard.php');
    exit;
}
```

---

## 4) Request Flow (Button Click to Response)

Example: clicking an Approve button

1. You click a link like: `/admin/approve.php?id=5`
2. Browser sends GET request to Apache
3. Apache runs `approve.php`
4. PHP reads `$_GET['id']`, runs UPDATE query
5. PHP redirects to dashboard
6. Browser requests dashboard page and shows updated data

Example: form submit (POST)

1. You fill a form and click submit
2. Browser sends POST request with form fields
3. PHP reads `$_POST` values
4. PHP runs INSERT query
5. PHP redirects to another page

---

## 5) File Organization (Simple Structure)

- `config/`: database connection
- `auth/`: login and register logic
- `admin/`: admin-only CRUD actions
- `doctor/`: doctor dashboard + register
- `includes/`: shared header/footer
- `assets/`: CSS, images

Tip: keep DB connection in one place and include it where needed.

---

## 6) Basic Best Practices

- Always validate and sanitize user input (`trim`, `filter_var`, `htmlspecialchars`).
- Use prepared statements for all queries with user data.
- Hash passwords and never store plain text.
- Redirect after a successful POST to avoid duplicate submits.
- Keep connection config separate from page logic.
- Use sessions to protect admin pages.

---

## How to Start (Quick Steps)

1. Start Apache and MySQL in XAMPP.
2. Copy the folder to `htdocs`.
3. Import the database SQL in phpMyAdmin.
4. Open `http://localhost/Doctor-Portel/index.php`.

If you want, I can also walk through the specific files in this project one-by-one.

---

# Login, Signup, and Sessions (Project Guide)

This section explains how login, signup, and sessions work in this project, using code snippets taken directly from the project.

## Login (Admin + Doctor)

Login is handled in `auth/login.php`. It validates input, checks the selected role, then verifies credentials.

```php
require_once('../config/config.php');

$role = $_GET['role'] ?? 'doctor';
if ($role !== 'admin' && $role !== 'doctor') {
  $role = 'doctor';
}

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
    // Role-specific login happens below.
  }
}
```

### Admin login check

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

### Doctor login check (includes approval status)

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

---

## Signup (Admin + Doctor)

Signup is handled in `auth/signup.php`, and `auth/register.php` just includes it.

```php
// auth/register.php
require_once __DIR__ . '/signup.php';
```

### Input handling + password hashing

```php
$role = $_POST['role'] ?? 'doctor';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$specialty = trim($_POST['specialty'] ?? '');
$password = $_POST['password'] ?? '';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

### Admin signup (insert into admins)

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

### Doctor signup (insert into doctors with pending status)

```php
$stmt = mysqli_prepare(
  $conn,
  "INSERT INTO doctors (name, email, phone, specialty, image, password, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);
mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $specialty, $imagePath, $hashed_password);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
```

---

## Sessions (Login persistence)

Sessions are started in `config/config.php`, then used in login, dashboard, and logout.

### Start session (config)

```php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
```

### Protect admin pages (example)

```php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: ../auth/login.php');
  exit;
}
```

### Logout (clear session + cookie)

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

---

## Quick Flow Summary

1. Signup inserts a new admin or doctor into the database.
2. Doctor signup sets status to `pending`.
3. Login checks email + password.
4. Admin login goes to admin dashboard.
5. Doctor login only works after approval.
6. Sessions store logged-in identity until logout.
