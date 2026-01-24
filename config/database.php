<?php
// ==========================================
// DATABASE.PHP - Connection File
// config/database.php
// ==========================================
define('DB_HOST', 'localhost');
define('DB_USER', 'iqbal');
define('DB_PASS', '');
define('DB_NAME', 'job_portal_v2');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>