<?php
// ===================================
// config/database.php
// Auto-detect Docker atau Native PHP
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
define('DB_PASS', '');
define('DB_NAME', 'job_portal');
define('DB_CHARSET', 'utf8mb4');

function getConnection() {
    $maxRetries = 5;
    $retryDelay = 2;
    
    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Log successful connection
            error_log("Database connected successfully to: " . DB_HOST);
            
            return $pdo;
            
        } catch (PDOException $e) {
            error_log("Database Connection Attempt " . ($i + 1) . " failed: " . $e->getMessage());
            
            if ($i < $maxRetries - 1) {
                error_log("Retrying in $retryDelay seconds...");
                sleep($retryDelay);
                continue;
            }
            
            // Error page
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
                        <p><strong>Environment:</strong> " . ($GLOBALS['isDocker'] ? 'Docker' : 'PHP Native') . "</p>
                        <p><strong>DB Host:</strong> <code>" . DB_HOST . "</code></p>
                        <p><strong>DB Name:</strong> <code>" . DB_NAME . "</code></p>
                        <p><strong>DB User:</strong> <code>" . DB_USER . "</code></p>
                        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    </div>
                    
                    <h3>🔍 Troubleshooting:</h3>
                    <ul>
                        <li>Cek container status: <code>docker compose ps</code></li>
                        <li>Cek database logs: <code>docker compose logs db</code></li>
                        <li>Test koneksi: <code>docker exec -it kasir_db mysql -uiqbal -p</code></li>
                        <li>Restart: <code>docker compose restart</code></li>
                    </ul>
                </div>
            </body>
            </html>
            ");
        }
    }
}
?>

