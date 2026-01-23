<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'iqbal');
define('DB_PASS', '#semarangwhj354iqbal#');
define('DB_NAME', 'job_portal');

// Membuat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>
