<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'doctor_system';

if (!function_exists('mysqli_connect')) {
    die('MySQLi extension is not enabled. Enable it in PHP/XAMPP first.');
}

// Connect to MySQL server first (without selecting database).
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die('Database server connection failed: ' . mysqli_connect_error());
}

// Create database if missing, then select it.
$createDatabaseSql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
if (!mysqli_query($conn, $createDatabaseSql)) {
    die('Unable to create database: ' . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $dbname)) {
    die('Unable to select database: ' . mysqli_error($conn));
}

mysqli_set_charset($conn, 'utf8');

// Keep tables ready so first run does not fail on login/signup pages.
$createAdminsTable = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

$createDoctorsTable = "
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    specialty VARCHAR(100) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'
)";

if (!mysqli_query($conn, $createAdminsTable)) {
    die('Unable to create admins table: ' . mysqli_error($conn));
}

if (!mysqli_query($conn, $createDoctorsTable)) {
    die('Unable to create doctors table: ' . mysqli_error($conn));
}

// Add new doctor profile columns in case table already existed.
$columnResult = mysqli_query($conn, "SHOW COLUMNS FROM doctors LIKE 'phone'");
if (!$columnResult || mysqli_num_rows($columnResult) === 0) {
    mysqli_query($conn, "ALTER TABLE doctors ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER email");
}

$columnResult = mysqli_query($conn, "SHOW COLUMNS FROM doctors LIKE 'specialty'");
if (!$columnResult || mysqli_num_rows($columnResult) === 0) {
    mysqli_query($conn, "ALTER TABLE doctors ADD COLUMN specialty VARCHAR(100) DEFAULT NULL AFTER phone");
}

$columnResult = mysqli_query($conn, "SHOW COLUMNS FROM doctors LIKE 'image'");
if (!$columnResult || mysqli_num_rows($columnResult) === 0) {
    mysqli_query($conn, "ALTER TABLE doctors ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER specialty");
}

/**
 * Resolve and prepare doctor profile image upload directory (project_root/uploads/doctors).
 *
 * @return string|false Absolute path with trailing slash, or false if unusable
 */
function ensure_doctor_upload_directory()
{
    $dir = dirname(__DIR__) . '/uploads/doctors';
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true)) {
            return false;
        }
    }
    if (!is_writable($dir)) {
        @chmod($dir, 0775);
    }
    return is_writable($dir) ? $dir . '/' : false;
}

require_once __DIR__ . '/image_url.php';
