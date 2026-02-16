<?php
// ================================================
// JobPortal - Database Config (FlintGo MySQL)
// ================================================

define('DB_HOST', 'flintgo-mysql');  // Nama container MySQL FlintGo
define('DB_PORT', 3306);             // Port internal Docker
define('DB_USER', 'jobportal_user');
define('DB_PASS', 'jobportal_pass');
define('DB_NAME', 'job_portal');
define('DB_CHARSET', 'utf8mb4');

$maxRetries = 5;
$retryDelay = 2;
$conn = null;

for ($i = 0; $i < $maxRetries; $i++) {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn) {
        mysqli_set_charset($conn, DB_CHARSET);
        error_log("✅ Database connected successfully to: " . DB_HOST);
        break;
    } else {
        error_log("⚠️ Database Connection Attempt " . ($i + 1) . " failed: " . mysqli_connect_error());
        
        if ($i < $maxRetries - 1) {
            error_log("🔄 Retrying in $retryDelay seconds...");
            sleep($retryDelay);
            continue;
        }
        
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Error - JobPortal</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    padding: 40px; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                .error-box { 
                    background: white; 
                    color: #333;
                    padding: 30px; 
                    border-radius: 15px; 
                    max-width: 800px;
                    margin: 0 auto;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                }
                h1 { color: #dc3545; margin-top: 0; }
                code { 
                    background: #f8f9fa; 
                    padding: 2px 8px; 
                    border-radius: 4px;
                    font-family: 'Courier New', monospace;
                    color: #e83e8c;
                }
                .info { 
                    background: #e7f3ff; 
                    padding: 15px; 
                    border-radius: 8px; 
                    margin: 15px 0;
                    border-left: 4px solid #0066cc;
                }
                .command {
                    background: #2d3748;
                    color: #68d391;
                    padding: 15px;
                    border-radius: 8px;
                    font-family: 'Courier New', monospace;
                    margin: 10px 0;
                    overflow-x: auto;
                }
                ul { line-height: 1.8; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>❌ Database Connection Failed</h1>
                
                <div class='info'>
                    <p><strong>Connection Details:</strong></p>
                    <p>🔌 Host: <code>" . DB_HOST . "</code></p>
                    <p>🔢 Port: <code>" . DB_PORT . "</code></p>
                    <p>🗄️ Database: <code>" . DB_NAME . "</code></p>
                    <p>👤 User: <code>" . DB_USER . "</code></p>
                    <p><strong>❌ Error:</strong> <code>" . htmlspecialchars(mysqli_connect_error()) . "</code></p>
                </div>
                
                <h3>🔍 Troubleshooting Steps:</h3>
                
                <h4>1️⃣ Check if MySQL container is running:</h4>
                <div class='command'>docker ps | grep flintgo-mysql</div>
                <p>Pastikan container <code>flintgo-mysql</code> berstatus <strong>Up</strong></p>
                
                <h4>2️⃣ Check if database exists:</h4>
                <div class='command'>docker exec -it flintgo-mysql mysql -u root -p -e \"SHOW DATABASES;\"</div>
                <p>Cek apakah database <code>job_portal</code> ada di list</p>
                
                <h4>3️⃣ Create database if not exists:</h4>
                <div class='command'>docker exec -it flintgo-mysql mysql -u root -p</div>
                <p>Kemudian jalankan:</p>
                <div class='command'>
CREATE DATABASE IF NOT EXISTS job_portal;<br>
CREATE USER IF NOT EXISTS 'jobportal_user'@'%' IDENTIFIED BY 'jobportal_pass';<br>
GRANT ALL PRIVILEGES ON job_portal.* TO 'jobportal_user'@'%';<br>
FLUSH PRIVILEGES;<br>
EXIT;
                </div>
                
                <h4>4️⃣ Check network connection:</h4>
                <div class='command'>docker network inspect flintgo_flintgo-network</div>
                <p>Pastikan container <code>jobportal_web</code> dan <code>flintgo-mysql</code> ada di network yang sama</p>
                
                <h4>5️⃣ Restart containers:</h4>
                <div class='command'>docker compose restart web</div>
                
                <hr>
                
                <h3>📚 Need More Help?</h3>
                <ul>
                    <li>Check logs: <code>docker compose logs -f web</code></li>
                    <li>Verify user permissions in MySQL</li>
                    <li>Make sure password is correct</li>
                </ul>
            </div>
        </body>
        </html>
        ");
    }
}

if (!$conn) {
    die("Failed to connect to database after $maxRetries attempts");
}
?>
