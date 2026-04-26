# 🚀 Quick Start Guide

## 1️⃣ Import Database Schema

```bash
# Option A: Using phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Click "Import"
3. Select db/schema.sql
4. Click "Go"

# Option B: Using MySQL Command Line
mysql -u root -p < db/schema.sql
```

---

## 2️⃣ File Locations & Access

| Purpose | URL | File |
|---------|-----|------|
| **Admin Login** | `http://localhost/doctor-portal/admin/login.php` | `admin/login.php` |
| **Admin Dashboard** | `http://localhost/doctor-portal/admin/dashboard.php` | `admin/dashboard.php` |
| **Doctor Register** | `http://localhost/doctor-portal/doctor/register.php` | `doctor/register.php` |
| **Doctor Login** | `http://localhost/doctor-portal/doctor/login.php` | `doctor/login.php` |

---

## 3️⃣ Demo Credentials

### Admin Account
```
Username: admin
Password: admin123
```

---

## 4️⃣ Core Files Breakdown

### 📁 `/config/db.php`
✓ Database connection  
✓ Error handling  
✓ Charset configuration  

### 📁 `/config/session.php`
✓ Secure session setup  
✓ CSRF token generation  
✓ Input sanitization  

### 📁 `/admin/login.php`
✓ Admin authentication  
✓ Prepared statements  
✓ Password verification  

### 📁 `/admin/dashboard.php`
✓ List pending doctors  
✓ Approve/Reject buttons  
✓ Statistics display  

### 📁 `/admin/process_action.php`
✓ Handle approve action  
✓ Handle reject action  
✓ Update doctor status  

### 📁 `/doctor/register.php`
✓ Doctor registration form  
✓ Input validation  
✓ Status = 'pending'  

### 📁 `/doctor/login.php`
✓ Doctor login form  
✓ Only approved doctors  
✓ Session management  

### 📁 `/db/schema.sql`
✓ Database structure  
✓ All tables  
✓ Default admin account  

---

## 5️⃣ Key Security Features

✅ **SQL Injection Prevention**
- Prepared statements with parameterized queries

✅ **Password Security**
- Bcrypt hashing (password_hash/password_verify)

✅ **Session Security**
- HTTPOnly cookies
- CSRF tokens
- SameSite=Lax

✅ **Input Validation**
- Type checking
- Length validation
- Email validation

✅ **Access Control**
- Require admin login
- Status-based doctor login

---

## 6️⃣ Step-by-Step Doctor Approval Workflow

```
STEP 1: Doctor Registers
   └─ doctor/register.php
   └─ Status: 'pending'
   └─ Data stored in doctors table

STEP 2: Admin Logs In
   └─ admin/login.php
   └─ Creates admin session

STEP 3: Admin Views Dashboard
   └─ admin/dashboard.php
   └─ Shows all pending doctors
   └─ Displays statistics

STEP 4: Admin Approves/Rejects
   └─ Clicks Approve or Reject button
   └─ admin/process_action.php handles it
   └─ Status updated to 'approved' or 'rejected'

STEP 5: Doctor Logs In
   └─ doctor/login.php
   └─ Only works if status = 'approved'
   └─ Session created successfully
```

---

## 7️⃣ Query Examples

### Get Pending Doctors
```php
$result = $conn->query(
    "SELECT * FROM doctors WHERE status = 'pending' ORDER BY created_at DESC"
);
```

### Approve Doctor
```php
$conn->query(
    "UPDATE doctors SET status = 'approved', approved_at = NOW(), 
     approved_by = {$admin_id} WHERE id = {$doctor_id}"
);
```

### Check Admin Login
```php
if (isset($_SESSION['admin_id'])) {
    echo "Admin is logged in";
}
```

### Verify Doctor is Approved
```php
$result = $conn->query(
    "SELECT * FROM doctors WHERE username = 'username' AND status = 'approved'"
);
```

---

## 8️⃣ Configuration Check

Before running, verify in `config/db.php`:

```php
define('DB_HOST', 'localhost');        ✓ Default
define('DB_USER', 'root');             ✓ Default
define('DB_PASS', '');                 ✓ Default (empty)
define('DB_NAME', 'doctor_portal');    ✓ Must match database name
```

---

## 9️⃣ Common Issues & Solutions

### ❌ "Connection failed"
**Solution:** Check MySQL is running, verify credentials

### ❌ "Table doesn't exist"
**Solution:** Import schema.sql to create tables

### ❌ "Admin can't login"
**Solution:** Check admin account exists in database

### ❌ "Doctor can't login after approval"
**Solution:** Verify status = 'approved' in database

### ❌ "CSRF token error"
**Solution:** Ensure session is started, don't disable cookies

---

## 🔟 Next Steps / Enhancements

1. **Email Notifications**
   - Send approval/rejection emails to doctors

2. **Password Reset**
   - Add forgot password functionality

3. **Brute Force Protection**
   - Limit login attempts per IP

4. **Admin Panel Features**
   - View all doctors (approved/rejected)
   - Edit doctor information
   - Delete accounts

5. **Doctor Dashboard**
   - View own profile
   - Update information
   - View appointments

6. **Logging & Audit Trail**
   - Log all admin actions
   - Track login history

---

## 📞 File Structure Summary

```
doctor-portal/
├── config/
│   ├── db.php                    [Database connection]
│   ├── session.php               [Session & security]
│   └── helpers.php               [Utility functions]
├── admin/
│   ├── login.php                 [Admin login]
│   ├── dashboard.php             [View pending doctors]
│   ├── process_action.php        [Approve/Reject]
│   └── logout.php                [Logout]
├── doctor/
│   ├── register.php              [Doctor registration]
│   └── login.php                 [Doctor login]
├── db/
│   ├── schema.sql                [Database schema]
│   └── security_enhancements.sql [Optional security tables]
├── README.md                     [Full documentation]
└── QUICKSTART.md                 [This file]
```

---

**Ready to go! 🎉 Start with admin login using demo credentials.**
