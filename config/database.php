<?php
// ===================================
// config/database.php
// Auto-detect Docker atau Native PHP
// ===================================

// Deteksi apakah running di Docker atau native
$isDocker = getenv('APACHE_DOCUMENT_ROOT') !== false || file_exists('/.dockerenv');

// Set database credentials
if ($isDocker) {
    define('DB_HOST', 'db');
} else {
    define('DB_HOST', '127.0.0.1');
}

define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'job_portal');

// Create mysqli connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Error</title>
        <style>
            body { 
                font-family: Arial; 
                padding: 40px; 
                background: #f5f5f5; 
                text-align: center;
            }
            .error-box { 
                background: white; 
                padding: 30px; 
                border-radius: 10px; 
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 { color: #dc3545; }
            code { 
                background: #f8f9fa; 
                padding: 2px 6px; 
                border-radius: 3px;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>❌ Database Connection Failed</h1>
            <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
            <hr>
            <h3>🔍 Troubleshooting:</h3>
            <ul style='text-align: left;'>
                <li>Pastikan MySQL/MariaDB sudah berjalan</li>
                <li>Cek kredensial database di <code>config/database.php</code></li>
                <li>Pastikan database <code>" . DB_NAME . "</code> sudah dibuat</li>
                <li>Jalankan: <code>mysql -u root -p < database_complete.sql</code></li>
            </ul>
        </div>
    </body>
    </html>
    ");
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');
?>