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
