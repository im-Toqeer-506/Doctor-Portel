<?php
// Database connection (edit these values to match your setup)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'doctor_system';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');
