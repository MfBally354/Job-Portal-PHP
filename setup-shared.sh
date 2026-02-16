#!/bin/bash

# ================================================
# JobPortal - Setup dengan MySQL Existing
# ================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
cat << "EOF"
   ___      _     ____            _        _ 
  |_  |    | |   |  _ \ ___  _ __| |_ __ _| |
    | | ___| |__ | |_) / _ \| '__| __/ _` | |
/\__/ // _ \ '_ \|  __/ (_) | |  | || (_| | |
\____/ \___/_.__/|_|   \___/|_|   \__\__,_|_|
                                              
         Shared MySQL Setup
EOF
echo -e "${NC}"

echo -e "${GREEN}Setup JobPortal dengan MySQL Existing${NC}"
echo "=========================================="
echo ""

# Detect existing MySQL
echo "🔍 Checking existing MySQL setup..."
echo ""

# Check for MySQL on port 3307
if lsof -i:3307 > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Found MySQL on port 3307${NC}"
    MYSQL_PORT=3307
elif docker ps | grep -q "3307->3306"; then
    echo -e "${GREEN}✅ Found MySQL container using port 3307${NC}"
    MYSQL_PORT=3307
else
    echo -e "${YELLOW}⚠️  No MySQL found on port 3307${NC}"
    MYSQL_PORT=3307
fi

# Check for phpMyAdmin on port 8091
if lsof -i:8091 > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Found phpMyAdmin on port 8091${NC}"
    PMA_PORT=8091
elif docker ps | grep -q "8091->80"; then
    echo -e "${GREEN}✅ Found phpMyAdmin container on port 8091${NC}"
    PMA_PORT=8091
else
    echo -e "${YELLOW}⚠️  No phpMyAdmin found on port 8091${NC}"
    PMA_PORT=8091
fi

echo ""
echo "================================================"
echo -e "${BLUE}Existing Infrastructure:${NC}"
echo "  MySQL:      localhost:$MYSQL_PORT"
echo "  phpMyAdmin: http://localhost:$PMA_PORT"
echo "================================================"
echo ""

# Ask user how they want to proceed
echo "How do you want to setup JobPortal?"
echo ""
echo "1) Use existing MySQL (Recommended)"
echo "   - Share MySQL with other projects"
echo "   - Save resources"
echo "   - Web app on port 8093"
echo ""
echo "2) Create new MySQL for JobPortal only"
echo "   - Isolated database"
echo "   - Uses more resources"
echo "   - New ports (8094, 3309)"
echo ""
read -p "Enter choice (1 or 2) [1]: " SETUP_CHOICE
SETUP_CHOICE=${SETUP_CHOICE:-1}

if [ "$SETUP_CHOICE" = "1" ]; then
    echo ""
    echo -e "${BLUE}📋 Setting up with SHARED MySQL...${NC}"
    echo ""
    
    # Check if MySQL is Docker container or host
    echo "Is your MySQL running in:"
    echo "1) Docker container"
    echo "2) Host machine (native MySQL/MariaDB)"
    echo ""
    read -p "Enter choice (1 or 2) [2]: " MYSQL_LOCATION
    MYSQL_LOCATION=${MYSQL_LOCATION:-2}
    
    if [ "$MYSQL_LOCATION" = "1" ]; then
        # Docker MySQL - use external network
        echo ""
        echo "Enter your MySQL container name:"
        read -p "Container name: " MYSQL_CONTAINER
        
        echo ""
        echo "Enter your Docker network name:"
        echo "(Run: docker network ls to see available networks)"
        read -p "Network name: " DOCKER_NETWORK
        
        # Use external network compose
        cp docker-compose-external-network.yml docker-compose.yml
        
        # Update container name and network
        sed -i "s/mysql_container_name/$MYSQL_CONTAINER/g" docker-compose.yml
        sed -i "s/your_existing_network_name/$DOCKER_NETWORK/g" docker-compose.yml
        
    else
        # Host MySQL - use host.docker.internal
        cp docker-compose-shared-mysql.yml docker-compose.yml
    fi
    
    echo ""
    echo -e "${GREEN}✅ docker-compose.yml configured!${NC}"
    echo ""
    
    # Database credentials
    echo "📊 Database Setup"
    echo "=================================="
    echo ""
    echo "Enter MySQL credentials (or press Enter for defaults):"
    echo ""
    read -p "Database name [job_portal]: " DB_NAME
    DB_NAME=${DB_NAME:-job_portal}
    
    read -p "Database user [jobportal_user]: " DB_USER
    DB_USER=${DB_USER:-jobportal_user}
    
    read -sp "Database password [jobportal_pass]: " DB_PASS
    echo ""
    DB_PASS=${DB_PASS:-jobportal_pass}
    
    read -sp "Root password: " ROOT_PASS
    echo ""
    
    echo ""
    echo "Creating database and user..."
    
    # Create database
    if [ "$MYSQL_LOCATION" = "1" ]; then
        # Docker MySQL
        docker exec $MYSQL_CONTAINER mysql -u root -p"$ROOT_PASS" -e "
        CREATE DATABASE IF NOT EXISTS $DB_NAME;
        CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
        GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
        FLUSH PRIVILEGES;
        " 2>/dev/null || echo -e "${YELLOW}⚠️  Could not create database. Please create manually.${NC}"
    else
        # Host MySQL
        mysql -u root -p"$ROOT_PASS" -e "
        CREATE DATABASE IF NOT EXISTS $DB_NAME;
        CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
        GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
        FLUSH PRIVILEGES;
        " 2>/dev/null || echo -e "${YELLOW}⚠️  Could not create database. Please create manually.${NC}"
    fi
    
    # Import database
    if [ -f "database_complete.sql" ]; then
        echo ""
        echo "📥 Importing database schema..."
        
        if [ "$MYSQL_LOCATION" = "1" ]; then
            docker exec -i $MYSQL_CONTAINER mysql -u $DB_USER -p"$DB_PASS" $DB_NAME < database_complete.sql
        else
            mysql -u $DB_USER -p"$DB_PASS" $DB_NAME < database_complete.sql
        fi
        
        echo -e "${GREEN}✅ Database imported!${NC}"
    fi
    
    # Update config/database.php
    echo ""
    echo "📝 Updating config/database.php..."
    
    cat > config/database.php << EOFPHP
<?php
// JobPortal - Database Config (Shared MySQL)
define('DB_HOST', '${MYSQL_LOCATION}' == '1' ? '${MYSQL_CONTAINER}' : 'host.docker.internal');
define('DB_PORT', ${MYSQL_PORT});
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('DB_NAME', '${DB_NAME}');
define('DB_CHARSET', 'utf8mb4');

\$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!\$conn) {
    die("
    <!DOCTYPE html>
    <html>
    <head><title>Database Error</title></head>
    <body>
        <h1>❌ Database Connection Failed</h1>
        <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
        <p>Check if MySQL is running and credentials are correct.</p>
    </body>
    </html>
    ");
}

mysqli_set_charset(\$conn, DB_CHARSET);
?>
EOFPHP
    
    echo -e "${GREEN}✅ Config updated!${NC}"
    
    WEB_PORT=8093
    
else
    # New MySQL setup
    echo ""
    echo -e "${BLUE}📋 Setting up with NEW MySQL...${NC}"
    cp docker-compose.yml docker-compose-original.yml
    WEB_PORT=8094
fi

echo ""
echo "🔨 Building Docker containers..."
docker compose build

echo ""
echo "🚀 Starting containers..."
docker compose up -d

echo ""
echo "⏳ Waiting for services..."
sleep 5

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "🌐 Access JobPortal:"
echo "   Web: http://localhost:$WEB_PORT"
echo ""
echo "📊 Existing Services:"
echo "   MySQL:      localhost:$MYSQL_PORT"
echo "   phpMyAdmin: http://localhost:$PMA_PORT"
echo ""
echo "👤 Default Login:"
echo "   Email:    admin@jobportal.com"
echo "   Password: password"
echo ""
echo "🔧 Useful Commands:"
echo "   docker compose logs -f web"
echo "   docker compose restart web"
echo "   docker compose down"
echo ""
echo -e "${GREEN}🎉 Enjoy JobPortal!${NC}"
echo ""
