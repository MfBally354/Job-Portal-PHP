<?php
require_once 'config/database.php';

if ($conn) {
    echo "✅ Database connected successfully!<br>";
    echo "Database: " . DB_NAME . "<br>";
    
    $result = mysqli_query($conn, "SHOW TABLES");
    echo "Tables found: " . mysqli_num_rows($result) . "<br>";
    
    while ($row = mysqli_fetch_array($result)) {
        echo "- " . $row[0] . "<br>";
    }
} else {
    echo "❌ Connection failed!";
}
?>