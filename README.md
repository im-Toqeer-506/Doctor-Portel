# Doctor Portal - Admin Management System

Complete PHP + MySQL admin system for managing doctor registrations with approval workflow.

## 📋 Project Structure

```
doctor-portal/
├── admin/                    # Admin panel files
│   ├── login.php            # Admin login page
│   ├── dashboard.php        # Main dashboard (pending doctors)
│   ├── process_action.php   # Handle approve/reject actions
│   └── logout.php           # Logout functionality
├── doctor/                  # Doctor portal files
│   ├── register.php         # Doctor registration form
│   └── login.php            # Doctor login (approved only)
├── config/                  # Configuration files
│   ├── db.php              # Database connection
│   ├── session.php         # Session management & security
│   └── helpers.php         # Helper functions
├── db/                     # Database files
│   └── schema.sql          # Database schema with tables
└── index.html              # Main page (HTML template)
```

## 🚀 Setup Instructions

### 1. Create Database

1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Copy & paste the entire content from `db/schema.sql` into the SQL tab
3. Click **Execute** to create the database and tables
4. Verify tables are created successfully

### 2. Database Configuration

Edit `config/db.php` and verify:
```php
define('DB_HOST', 'localhost');     // Usually 'localhost'
define('DB_USER', 'root');           // Default XAMPP user
define('DB_PASS', '');               // Default XAMPP password (empty)
define('DB_NAME', 'doctor_portal');  // Database name
```

### 3. Start XAMPP

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**
3. Place project in `C:\xampp\htdocs\doctor-portal\` (Windows)
   or `/Applications/XAMPP/htdocs/doctor-portal/` (Mac)
   or `/opt/lampp/htdocs/doctor-portal/` (Linux)

### 4. Access Application

**Admin:**
- URL: `http://localhost/doctor-portal/admin/login.php`
- Username: `admin`
- Password: `admin123`

**Doctor Registration:**
- URL: `http://localhost/doctor-portal/doctor/register.php`

**Doctor Login:**
- URL: `http://localhost/doctor-portal/doctor/login.php`

---

## 🔐 Security Features Implemented

### ✓ SQL Injection Prevention
- **Prepared Statements** used for all database queries
- User input never directly concatenated into SQL
- Parameter binding protects against SQL attacks

Example:
```php
$stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ? AND status = ?");
$stmt->bind_param('is', $id, $status);
```

### ✓ Password Security
- **Bcrypt hashing** with `password_hash()` and `password_verify()`
- Never stored as plain text
- Default password in schema is hashed

### ✓ Session Management
- **Secure session cookies** (HTTPOnly, SameSite=Lax)
- Session timeout capabilities
- CSRF token protection on forms
- Session hijacking prevention

### ✓ Input Validation & Sanitization
- All inputs validated before processing
- `sanitize_input()` removes dangerous characters
- Email validation with `filter_var()`
- Type casting for numeric values

### ✓ Access Control
- Admin authentication required for dashboard
- Doctors can only login if **status = 'approved'**
- `require_admin_login()` enforces access restrictions

### ✓ CSRF Protection
- Unique tokens generated per session
- Token verified on form submission
- `verify_csrf_token()` prevents cross-site attacks

---

## 📊 Database Schema

### Admins Table
```sql
id, username, email, password, full_name, status, created_at, updated_at
```

### Doctors Table
```sql
id, username, email, password, full_name, specialization, phone,
qualification, experience, clinic_address, status (pending/approved/rejected),
rejection_reason, created_at, updated_at, approved_at, approved_by
```

### Patients Table
```sql
id, username, email, password, full_name, phone, date_of_birth,
gender, address, created_at, updated_at
```

### Appointments Table
```sql
id, doctor_id, patient_id, appointment_date, status,
notes, created_at, updated_at
```

### Admin Login History Table
```sql
id, admin_id, login_time, ip_address, user_agent
```

---

## 🎯 Workflow

### Doctor Registration & Approval Flow

```
1. Doctor Registration (doctor/register.php)
   ↓
   → Status = 'pending' (stored in DB)
   ↓
2. Admin Dashboard (admin/dashboard.php)
   ↓
   → View all pending doctors
   ↓
3. Approve/Reject Action (admin/process_action.php)
   ↓
   → Status = 'approved' OR 'rejected'
   ↓
4. Doctor Login
   ↓
   → Only 'approved' doctors can login
   → Rejected doctors cannot access
```

---

## 📝 Code Examples

### Example 1: Secure Login Query
```php
// Prepared statement prevents SQL injection
$stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
```

### Example 2: Password Verification
```php
// Secure password checking
if (password_verify($password, $admin['password'])) {
    // Password is correct
    $_SESSION['admin_id'] = $admin['id'];
}
```

### Example 3: CSRF Protection
```php
// Generate token
$token = get_csrf_token();

// Verify on form submission
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('CSRF token verification failed');
}
```

### Example 4: Approve Doctor
```php
$stmt = $conn->prepare(
    "UPDATE doctors SET status = 'approved', approved_at = NOW(), 
     approved_by = ? WHERE id = ?"
);
$stmt->bind_param('ii', $_SESSION['admin_id'], $doctor_id);
$stmt->execute();
```

---

## 🛠️ API Endpoints

### Admin
- `POST /admin/login.php` - Admin login
- `GET /admin/dashboard.php` - View pending doctors
- `POST /admin/process_action.php` - Approve/Reject doctors
- `GET /admin/logout.php` - Logout

### Doctor
- `POST /doctor/register.php` - Doctor registration
- `POST /doctor/login.php` - Doctor login (approved only)

---

## 🔧 Customization

### Add Email Notifications
Modify `admin/process_action.php`:
```php
// After approval/rejection
$to = $doctor['email'];
$subject = "Application Status";
$message = "Your application has been " . $action;
mail($to, $subject, $message);
```

### Add Role-Based Access Control (RBAC)
Extend admin roles:
```php
CREATE TABLE admin_roles (
    id INT PRIMARY KEY,
    admin_id INT,
    role ENUM('super_admin', 'moderator', 'viewer')
);
```

### Add Activity Logging
Table already created: `admin_login_history`
Extend with general activity logs for audit trail.

---

## ⚠️ Production Checklist

Before deploying to production:

- [ ] Change default admin password
- [ ] Set `define('DB_PASS', '')` to actual password
- [ ] Set `secure => true` in session config (HTTPS only)
- [ ] Disable error reporting: `mysqli_report(0)`
- [ ] Use environment variables for sensitive data
- [ ] Enable SSL/TLS (HTTPS)
- [ ] Regular database backups
- [ ] Rate limiting on login attempts
- [ ] Add logging for all admin actions
- [ ] Monitor for suspicious activities

---

## 📚 Key Functions

| Function | Purpose |
|----------|---------|
| `is_admin_logged_in()` | Check if admin session exists |
| `require_admin_login()` | Redirect if not authenticated |
| `sanitize_input()` | Remove XSS threats |
| `verify_csrf_token()` | Validate form tokens |
| `get_csrf_token()` | Generate new token |
| `password_hash()` | Hash passwords with bcrypt |
| `password_verify()` | Compare password with hash |

---

## 🐛 Troubleshooting

### Error: "Connection failed"
- Check MySQL is running
- Verify database credentials in `config/db.php`
- Ensure database `doctor_portal` exists

### Error: "Session not working"
- Check `session_status() === PHP_SESSION_NONE`
- Verify cookies are enabled in browser
- Clear browser cache and cookies

### Doctor can't login after approval
- Verify status = 'approved' in database
- Check password hash is correct
- Ensure `password_verify()` returns true

### Admin can't see pending doctors
- Verify doctor status = 'pending' in database
- Check pagination offset
- Clear browser cache

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Verify database schema matches `db/schema.sql`
4. Check error logs in browser console

---

## 📄 License

This project is provided as-is for educational purposes.

---

**Last Updated:** April 26, 2026
