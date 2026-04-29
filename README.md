# Doctor Approval Admin Dashboard (Simple CRUD)

Beginner-friendly PHP + MySQL project. No login system.

## Requirements
- XAMPP (Apache + MySQL)
- PHP (procedural)
- MySQL / phpMyAdmin

## Folder Structure
/project-root
├── admin/
│   ├── dashboard.php
│   ├── approve.php
│   └── delete.php
├── doctor/
│   └── register.php
├── config/
│   └── config.php
├── auth/ (dummy only)
│   ├── login.php
│   └── signup.php
├── assets/
│   └── style.css
└── index.php

## Database Setup (phpMyAdmin)
1. Open http://localhost/phpmyadmin
2. Create a database named: doctor_system
3. Run this SQL:

```sql
CREATE DATABASE doctor_system;

USE doctor_system;

CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL
);
```

## Configure Database Connection
Open config/config.php and update if needed:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'doctor_system';
```

## How to Run (XAMPP)
1. Start Apache and MySQL in XAMPP.
2. Move project folder to:
   - C:\xampp\htdocs\Doctor-Portel (Windows)
   - /opt/lampp/htdocs/Doctor-Portel (Linux)
3. Open in browser:
   - Home: http://localhost/Doctor-Portel/index.php
   - Doctor Registration: http://localhost/Doctor-Portel/doctor/register.php
   - Admin Dashboard: http://localhost/Doctor-Portel/admin/dashboard.php

## System Flow (Simple)
1. Doctor submits the registration form.
2. Data is saved in the database with status = 'pending'.
3. Admin opens dashboard and sees all pending doctors.
4. Admin clicks Approve (status becomes 'approved') or Delete.

## Notes
- No login or authentication is included.
- Uses only procedural PHP and mysqli.
- CSS is intentionally simple for beginners.
