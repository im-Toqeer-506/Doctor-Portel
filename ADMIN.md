# Admin Functionalities Guide (Viva Ready)

This guide explains the **exact admin flow** in your project so you can present it clearly in viva.

It covers:
- Admin signup
- Admin login
- Admin dashboard access control
- Approve doctor
- Reject doctor
- Delete doctor
- How doctor management works end-to-end

---

## 1) Core Idea of Admin Module

In this project, admin is responsible for controlling doctor onboarding.

- Doctor registers with `pending` status.
- Admin logs in and opens dashboard.
- Admin can approve, reject, or delete doctors.
- Only approved doctors can log in as doctor.

---

## 2) Important Files for Admin Flow

- `config/config.php` -> DB connection, session start, table setup
- `auth/signup.php` -> Admin + Doctor signup logic
- `auth/register.php` -> Route alias for signup
- `auth/login.php` -> Admin + Doctor login logic
- `admin/dashboard.php` -> Main admin panel + doctor management actions
- `admin/approve.php` -> Legacy approve endpoint (GET based)
- `admin/delete.php` -> Legacy delete endpoint (GET based)
- `admin/approved.php` -> List of approved doctors

---

## 3) Step-by-Step Flow You Can Speak in Viva

## Step A: Admin Signup

Admin account is created from `auth/signup.php` when role is `admin`.

```php
if ($role === 'admin') {
    $check = mysqli_prepare($conn, 'SELECT id FROM admins WHERE email = ?');
    if ($check) {
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        $exists = $result && mysqli_fetch_assoc($result);
        mysqli_stmt_close($check);
    } else {
        $exists = false;
    }

    if ($exists) {
        $error = 'An admin with this email already exists.';
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashed_password);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}
```

### Viva explanation
- First it checks duplicate email in `admins` table.
- Password is saved hashed using `password_hash(...)`.
- New admin record is inserted only if email does not exist.

---

## Step B: Admin Login

Admin login is handled in `auth/login.php`.

```php
if ($role === 'admin') {
    $stmt = mysqli_prepare($conn, 'SELECT id, name, password FROM admins WHERE email = ?');
    if ($stmt) {
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

        $error = 'Invalid admin credentials.';
    }
}
```

### Viva explanation
- Email lookup happens in `admins`.
- Password is verified using `password_verify(...)`.
- On success, session variables are created.
- Then redirect to `admin/dashboard.php`.

---

## Step C: Dashboard Access Protection (Authorization)

Each admin page checks session role before showing data.

```php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
```

### Viva explanation
- This prevents unauthorized users from opening admin URLs directly.
- If role is not admin, user is redirected to login page.

---

## Step D: Admin Dashboard Data Loading

Dashboard fetches all doctors for management.

```php
$doctors = [];
$result = mysqli_query($conn, 'SELECT id, name, email, phone, specialty, image, status FROM doctors ORDER BY id DESC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
}
```

It also calculates summary counters:

```php
$totalDoctors = count($doctors);
$pendingDoctors = 0;
$approvedDoctors = 0;
$rejectedDoctors = 0;
foreach ($doctors as $doctorRow) {
    if ($doctorRow['status'] === 'approved') {
        $approvedDoctors++;
    } elseif ($doctorRow['status'] === 'rejected') {
        $rejectedDoctors++;
    } else {
        $pendingDoctors++;
    }
}
```

### Viva explanation
- Admin gets one table of all doctors.
- Status stats are shown as dashboard cards.

---

## Step E: Approve / Reject / Delete Actions (POST-based in dashboard)

Current primary management actions are handled in `admin/dashboard.php` itself using POST.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $doctorId = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;

    if ($action === 'status' && $doctorId > 0) {
        $status = $_POST['status'] ?? '';
        if ($status === 'approved' || $status === 'rejected') {
            $stmt = mysqli_prepare($conn, 'UPDATE doctors SET status = ? WHERE id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $status, $doctorId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header('Location: dashboard.php?msg=status_updated');
                exit;
            }
        }
    } elseif ($action === 'delete' && $doctorId > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM doctors WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $doctorId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php?msg=deleted');
            exit;
        }
    }
}
```

### Viva explanation
- Dashboard receives form POST requests.
- `action=status` updates doctor status to `approved` or `rejected`.
- `action=delete` removes doctor row.
- After action, redirect is used (PRG pattern: Post -> Redirect -> Get).

---

## Step F: Dashboard Buttons That Trigger Actions

Each row has three forms.

```php
<form method="POST" action="">
    <input type="hidden" name="action" value="status">
    <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
    <input type="hidden" name="status" value="approved">
    <button class="btn btn-xs" type="submit">Approve</button>
</form>

<form method="POST" action="">
    <input type="hidden" name="action" value="status">
    <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
    <input type="hidden" name="status" value="rejected">
    <button class="btn danger btn-xs" type="submit">Reject</button>
</form>

<form method="POST" action="" onsubmit="return confirm('Delete this doctor?');">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
    <button class="btn danger ghost-danger btn-xs" type="submit">Delete</button>
</form>
```

### Viva explanation
- Hidden inputs send action + doctor id + target status.
- This makes one dashboard endpoint handle all operations cleanly.

---

## 4) Legacy Endpoints (Still Present)

You also have separate GET-based files:

### `admin/approve.php`

```php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "UPDATE doctors SET status = 'approved' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
header('Location: dashboard.php?msg=approved');
exit;
```

### `admin/delete.php`

```php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM doctors WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
header('Location: dashboard.php?msg=deleted');
exit;
```

### Viva note
- These endpoints are functional.
- But dashboard POST approach is better and currently used in the UI.

---

## 5) Approved Doctors View for Admin

`admin/approved.php` shows only approved doctors:

```php
$result = mysqli_query(
    $conn,
    "SELECT id, name, email, phone, specialty, status FROM doctors WHERE status = 'approved' ORDER BY id DESC"
);
```

### Viva explanation
- This is a filtered management/report view for admin.
- It verifies approved records after moderation.

---

## 6) How Doctor Approval Connects to Login

Doctor login logic enforces status check:

```php
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
- This is the main business rule: only approved doctors can access doctor dashboard.
- So admin moderation directly controls doctor system access.

---

## 7) Complete End-to-End Admin Flow (One Story)

1. Admin signs up from `auth/register.php` (internally `signup.php`).
2. Admin logs in from `auth/login.php` with role `admin`.
3. Session is created (`user_role = admin`).
4. Admin opens `admin/dashboard.php`.
5. Dashboard loads all doctors and status counts.
6. Admin clicks Approve/Reject/Delete.
7. POST action updates `doctors` table.
8. Dashboard reloads and reflects latest doctor state.
9. Approved doctors can now log in; pending/rejected cannot.

---

## 8) Viva-Friendly Quick Questions and Answers

### Q1: How is admin authentication implemented?
- Admin credentials are checked from `admins` table.
- Password comparison uses `password_verify`.
- On success, role is stored in session and user is redirected.

### Q2: How do you secure admin pages?
- Role-based guard:
  - `$_SESSION['user_role']` must exist
  - and must equal `admin`
- Otherwise redirect to login.

### Q3: How do approve/reject/delete work technically?
- Dashboard form submits `action` and `doctor_id`.
- Backend uses prepared statements (`UPDATE`/`DELETE`).
- Then redirects to avoid duplicate form submission.

### Q4: Why can pending doctor not log in?
- Login checks `doctors.status`.
- Only `approved` path redirects to doctor dashboard.

### Q5: Where is doctor management centralized?
- In `admin/dashboard.php`, which handles:
  - listing doctors
  - status updates
  - deletion
  - statistics cards

---

## 9) Short Presentation Script (Use in Viva)

"In my project, admin module handles doctor onboarding and moderation.  
Admin is registered in `admins` table and authenticated with hashed password verification.  
After login, session-based role guard protects the admin dashboard.  
Dashboard fetches all doctors and gives Approve, Reject, Delete actions through POST forms.  
When admin approves a doctor, doctor status changes from `pending` to `approved`, and only then doctor login is allowed.  
So the core flow is secure authentication + role authorization + controlled doctor lifecycle management."

