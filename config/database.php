<?php
// ===================================
// config/database.php
// Auto-detect Docker atau Native PHP
// Menggunakan MySQLi
// ===================================

// Deteksi apakah running di Docker atau native
$isDocker = getenv('APACHE_DOCUMENT_ROOT') !== false || file_exists('/.dockerenv');

// Set database host berdasarkan environment
if ($isDocker) {
    // Running di Docker - gunakan nama service
    define('DB_HOST', 'db');
} else {
    // Running di PHP native - gunakan localhost
    define('DB_HOST', '127.0.0.1');
}

define('DB_USER', 'iqbal');
define('DB_PASS', '#semarangwhj354iqbal#');
define('DB_NAME', 'job_portal');
define('DB_CHARSET', 'utf8mb4');

$maxRetries = 5;
$retryDelay = 2;
$conn = null;

for ($i = 0; $i < $maxRetries; $i++) {
    // Attempt connection
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn) {
        // Set charset
        mysqli_set_charset($conn, DB_CHARSET);
        
        // Log successful connection
        error_log("Database connected successfully to: " . DB_HOST);
        break;
        
    } else {
        error_log("Database Connection Attempt " . ($i + 1) . " failed: " . mysqli_connect_error());
        
        if ($i < $maxRetries - 1) {
            error_log("Retrying in $retryDelay seconds...");
            sleep($retryDelay);
            continue;
        }
        
        // Final attempt failed - show error page
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Error</title>
            <style>
                body { font-family: Arial; padding: 40px; background: #f5f5f5; }
                .error-box { 
                    background: white; 
                    padding: 30px; 
                    border-radius: 10px; 
                    max-width: 800px;
                    margin: 0 auto;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 { color: #dc3545; }
                code { 
                    background: #f8f9fa; 
                    padding: 2px 6px; 
                    border-radius: 3px;
                    font-family: monospace;
                }
                .info { 
                    background: #e7f3ff; 
                    padding: 15px; 
                    border-radius: 5px; 
                    margin: 15px 0;
                    border-left: 4px solid #0066cc;
                }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>❌ Database Connection Failed</h1>
                
                <div class='info'>
                    <p><strong>Environment:</strong> " . ($isDocker ? 'Docker' : 'PHP Native') . "</p>
                    <p><strong>DB Host:</strong> <code>" . DB_HOST . "</code></p>
                    <p><strong>DB Name:</strong> <code>" . DB_NAME . "</code></p>
                    <p><strong>DB User:</strong> <code>" . DB_USER . "</code></p>
                    <p><strong>Error:</strong> " . htmlspecialchars(mysqli_connect_error()) . "</p>
                </div>
                
                <h3>🔍 Troubleshooting:</h3>
                <ul>
                    <li>Pastikan MySQL/MariaDB sudah running</li>
                    <li>Cek username dan password database</li>
                    <li>Pastikan database 'job_portal' sudah dibuat</li>
                    <li>Import file database_complete.sql terlebih dahulu</li>
                    <li>Jika pakai Docker: <code>docker compose ps</code></li>
                    <li>Jika pakai Docker: <code>docker compose logs db</code></li>
                    <li>Restart service: <code>docker compose restart</code> atau restart MySQL</li>
                </ul>
                
                <h3>📝 Quick Fix:</h3>
                <ol>
                    <li>Buka phpMyAdmin atau MySQL client</li>
                    <li>Buat database: <code>CREATE DATABASE job_portal;</code></li>
                    <li>Import file: <code>database_complete.sql</code></li>
                    <li>Refresh halaman ini</li>
                </ol>
            </div>
        </body>
        </html>
        ");
    }
}

// Check if connection is established
if (!$conn) {
    die("Failed to connect to database after $maxRetries attempts");
}
?>
