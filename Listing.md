# Listing Functionality Guide (Viva Ready)

This guide explains the listing functionality in your project in a presentation-friendly way.

It covers:
- Home page doctor preview listing
- Full doctors directory listing
- How approved filter works
- How summary cards are calculated
- How profile image fallback works
- How data is rendered safely in UI

---

## 1) Listing Module Goal

The listing module shows doctors to public users, but only those approved by admin.

- Home page (`index.php`) shows latest **6 approved doctors**
- Listing page (`doctors.php`) shows **all approved doctors**
- Pending and rejected doctors are hidden from public view

---

## 2) Files Used in Listing Flow

- `index.php` -> Home page preview cards
- `doctors.php` -> Full approved doctors directory
- `config/image_url.php` -> Safe profile image URL helper + fallback
- `config/config.php` -> DB connection and shared setup

---

## 3) Home Page Listing Flow (`index.php`)

### Query: fetch latest approved doctors

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

### Viva explanation
- Uses prepared statement to fetch data.
- Filter `WHERE status = 'approved'` is the main business rule.
- `ORDER BY id DESC LIMIT 6` gives latest six records.

### UI render: home doctor cards

```php
<?php if (!empty($approvedDoctors)): ?>
    <div class="doctor-cards-grid">
        <?php foreach ($approvedDoctors as $doctor): ?>
            <article class="doctor-card">
                <img
                    src="<?php echo htmlspecialchars(doctor_profile_image_url($doctor['image'] ?? null)); ?>"
                    alt="<?php echo htmlspecialchars($doctor['name']); ?>"
                >
                <div class="doctor-card-body">
                    <div class="doctor-card-status">
                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <span class="badge approved">Approved</span>
                    </div>
                    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialty'] ?: 'General'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($doctor['phone'] ?: 'Not provided'); ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p>No approved doctors to show yet.</p>
    </div>
<?php endif; ?>
```

### Viva explanation
- `foreach` renders one card per doctor.
- `htmlspecialchars(...)` protects output from HTML injection.
- Fallback text handles missing specialization/phone gracefully.

---

## 4) Full Listing Page Flow (`doctors.php`)

### Query: fetch all approved doctors

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

### Viva explanation
- Uses same approved filter as home page.
- Here there is no `LIMIT`, so full directory is shown.

### Summary cards logic in listing page

```php
<p><?php echo (int)count($doctors); ?></p>

<p><?php echo !empty($doctors) ? count(array_unique(array_filter(array_map(static function ($row) { return trim((string)$row['specialty']); }, $doctors)))) : 0; ?></p>
```

### Viva explanation
- First summary card shows total approved doctors.
- Second summary computes unique non-empty specialties dynamically.

### UI render: full directory cards

```php
<?php if (!empty($doctors)): ?>
    <?php foreach ($doctors as $doctor): ?>
        <article class="doctor-card">
            <img
                src="<?php echo htmlspecialchars(doctor_profile_image_url($doctor['image'] ?? null)); ?>"
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
    <div class="empty-state">
        <p>No approved doctors available yet.</p>
    </div>
<?php endif; ?>
```

### Viva explanation
- Card layout gives readable doctor profile information.
- Empty-state UI is shown when no approved doctors exist.

---

## 5) Profile Image Resolution and Fallback (`config/image_url.php`)

This helper ensures listing images are safe and stable.

```php
function doctor_profile_image_url(?string $storedPath): string
{
    $placeholder = 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=60';
    if ($storedPath === null) {
        return $placeholder;
    }
    $storedPath = trim($storedPath);
    if ($storedPath === '') {
        return $placeholder;
    }
    if (strpos($storedPath, '..') !== false) {
        return $placeholder;
    }
    $normalized = str_replace('\\', '/', $storedPath);
    $fullPath = dirname(__DIR__) . '/' . ltrim($normalized, '/');
    if (!is_file($fullPath)) {
        return $placeholder;
    }
    $base = portal_base_url();
    $urlPath = '/' . ltrim($normalized, '/');
    return ($base === '' ? '' : rtrim($base, '/')) . $urlPath;
}
```

### Viva explanation
- Uses a stock placeholder if image path is missing/invalid.
- Rejects path traversal (`..`) for safety.
- Converts filesystem path into public URL only if file exists.

---

## 6) End-to-End Listing Request Flow (Explain to Teacher)

1. User opens `index.php` or `doctors.php`.
2. PHP loads `config/config.php` and creates DB connection.
3. SQL query fetches doctors with `status='approved'`.
4. Results are stored in array (`$approvedDoctors` or `$doctors`).
5. Page loops over array and builds cards in HTML.
6. `doctor_profile_image_url(...)` resolves image with fallback.
7. Browser receives final rendered page.

---

## 7) Key Viva Points (One-Liners)

- Listing page is role-independent/public, but data is status-controlled.
- Core filter: `WHERE status = 'approved'`.
- Home page uses limited preview; directory page shows all.
- UI output is escaped with `htmlspecialchars`.
- Image helper improves reliability and security of profile pictures.
- Empty-state handling avoids blank or broken layouts.

---

## 8) 45-Second Viva Script

"In my listing module, I only display admin-approved doctors.  
The home page shows latest six approved doctors using a limited query, while the directory page shows all approved doctors.  
Each card is rendered from database results with safe output escaping.  
Profile image rendering is handled by a helper that validates path and falls back to a placeholder if image is missing.  
So the listing flow is: query approved doctors -> map data into cards -> show stable, safe public directory UI."

