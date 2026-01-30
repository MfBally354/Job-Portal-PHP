#!/bin/bash

# =================================================
# JobPortal - Native MySQL Setup for Raspberry Pi
# =================================================

set -e

echo "🍓 JobPortal - Native MySQL Setup"
echo "=================================="
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Database credentials
DB_NAME="job_portal"
DB_USER="jobportal_user"
DB_PASS="jobportal_pass"
DB_ROOT_PASS=""

# Get Pi IP
PI_IP=$(hostname -I | awk '{print $1}')

echo -e "${BLUE}📍 Raspberry Pi IP: $PI_IP${NC}"
echo ""

# Check if MySQL/MariaDB is installed
echo "🔍 Checking MySQL/MariaDB..."
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✅ MySQL/MariaDB found${NC}"
else
    echo -e "${YELLOW}⚠️  MySQL/MariaDB not found. Installing...${NC}"
    sudo apt update
    sudo apt install mariadb-server mariadb-client -y
    echo -e "${GREEN}✅ MariaDB installed${NC}"
fi

# Start MySQL service
echo ""
echo "🚀 Starting MySQL service..."
sudo systemctl start mariadb
sudo systemctl enable mariadb
echo -e "${GREEN}✅ MySQL service started${NC}"

# Get root password
echo ""
echo "🔐 MySQL Setup"
read -sp "Enter MySQL root password (press Enter if none): " DB_ROOT_PASS
echo ""

# Create database and user
echo ""
echo "📊 Creating database and user..."

if [ -z "$DB_ROOT_PASS" ]; then
    # No root password
    sudo mysql << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF
else
    # With root password
    mysql -u root -p"$DB_ROOT_PASS" << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF
fi

echo -e "${GREEN}✅ Database and user created${NC}"

# Import database
echo ""
if [ -f "database_complete.sql" ]; then
    echo "📥 Importing database schema..."
    if [ -z "$DB_ROOT_PASS" ]; then
        sudo mysql $DB_NAME < database_complete.sql
    else
        mysql -u root -p"$DB_ROOT_PASS" $DB_NAME < database_complete.sql
    fi
    echo -e "${GREEN}✅ Database imported${NC}"
else
    echo -e "${YELLOW}⚠️  database_complete.sql not found. Skipping import.${NC}"
fi

# Configure MySQL to listen on all interfaces
echo ""
echo "🔧 Configuring MySQL to accept remote connections..."

CONFIG_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
if [ -f "$CONFIG_FILE" ]; then
    sudo sed -i 's/bind-address.*/bind-address = 0.0.0.0/' $CONFIG_FILE
    sudo systemctl restart mariadb
    echo -e "${GREEN}✅ MySQL configured${NC}"
else
    echo -e "${YELLOW}⚠️  Config file not found at $CONFIG_FILE${NC}"
    echo "   Manually edit MySQL config to set: bind-address = 0.0.0.0"
fi

# Update database config file
echo ""
echo "📝 Updating config/database.php..."

cat > config/database.php << 'EOFCONFIG'
<?php
// Auto-detect Docker or Native PHP
$isDocker = getenv('APACHE_DOCUMENT_ROOT') !== false || file_exists('/.dockerenv');

if ($isDocker) {
    // Docker - use Pi IP address
    define('DB_HOST', 'REPLACE_PI_IP');
} else {
    // Native PHP
    define('DB_HOST', '127.0.0.1');
}

define('DB_USER', 'jobportal_user');
define('DB_PASS', 'jobportal_pass');
define('DB_NAME', 'job_portal');
define('DB_CHARSET', 'utf8mb4');

$maxRetries = 5;
$retryDelay = 2;
$conn = null;

for ($i = 0; $i < $maxRetries; $i++) {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn) {
        mysqli_set_charset($conn, DB_CHARSET);
        error_log("Database connected successfully to: " . DB_HOST);
        break;
    } else {
        error_log("Database Connection Attempt " . ($i + 1) . " failed: " . mysqli_connect_error());
        
        if ($i < $maxRetries - 1) {
            error_log("Retrying in $retryDelay seconds...");
            sleep($retryDelay);
            continue;
        }
        
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
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>❌ Database Connection Failed</h1>
                <p><strong>Host:</strong> " . DB_HOST . "</p>
                <p><strong>Error:</strong> " . htmlspecialchars(mysqli_connect_error()) . "</p>
                <p>Check MySQL is running: <code>sudo systemctl status mariadb</code></p>
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
EOFCONFIG

# Replace PI_IP placeholder
sed -i "s/REPLACE_PI_IP/$PI_IP/" config/database.php

echo -e "${GREEN}✅ Config updated${NC}"

# Setup Docker Compose for web only
echo ""
echo "🐳 Setting up Docker Compose..."

if [ -f "docker-compose-native-db.yml" ]; then
    cp docker-compose-native-db.yml docker-compose.yml
    echo -e "${GREEN}✅ Using docker-compose-native-db.yml${NC}"
else
    echo -e "${YELLOW}⚠️  docker-compose-native-db.yml not found${NC}"
fi

# Test database connection
echo ""
echo "🧪 Testing database connection..."

if mysql -u $DB_USER -p$DB_PASS -h 127.0.0.1 $DB_NAME -e "SHOW TABLES;" > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Database connection successful${NC}"
else
    echo -e "${RED}❌ Database connection failed${NC}"
    echo "   Try manually: mysql -u $DB_USER -p$DB_PASS -h 127.0.0.1 $DB_NAME"
fi

# Build and start Docker
echo ""
read -p "Build and start Docker containers now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "🔨 Building Docker containers..."
    docker compose build
    
    echo ""
    echo "🚀 Starting containers..."
    docker compose up -d
    
    echo ""
    echo "📊 Container status:"
    docker compose ps
fi

# Summary
echo ""
echo "=========================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "📊 Database Info:"
echo "   Host:     $PI_IP (from Docker) or localhost (from host)"
echo "   Database: $DB_NAME"
echo "   User:     $DB_USER"
echo "   Password: $DB_PASS"
echo ""
echo "🌐 Access Application:"
echo "   Web: http://$PI_IP:8091"
echo ""
echo "🔧 Useful Commands:"
echo "   Check MySQL:  sudo systemctl status mariadb"
echo "   MySQL client: mysql -u $DB_USER -p$DB_PASS $DB_NAME"
echo "   Docker logs:  docker compose logs -f"
echo "   Restart:      docker compose restart"
echo ""
echo "🎯 Next Steps:"
echo "   1. Access http://$PI_IP:8091"
echo "   2. Login with: admin@jobportal.com / password"
echo "   3. Change default password!"
echo ""
echo "🍓 Enjoy JobPortal on Raspberry Pi!"
echo ""
