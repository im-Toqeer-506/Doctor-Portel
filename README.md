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
