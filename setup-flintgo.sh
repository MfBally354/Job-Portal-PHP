#!/bin/bash

# ================================================
# JobPortal - Setup untuk FlintGo MySQL
# ================================================

set -e

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
                                              
      Setup with FlintGo MySQL
EOF
echo -e "${NC}"

echo "=========================================="
echo -e "${GREEN}JobPortal + FlintGo MySQL Setup${NC}"
echo "=========================================="
echo ""

# Step 1: Info
echo -e "${BLUE}📋 Detected Configuration:${NC}"
echo "  MySQL Container: flintgo-mysql"
echo "  MySQL Port:      3307 (external)"
echo "  Network:         flintgo_flintgo-network"
echo "  phpMyAdmin:      http://localhost:8091"
echo "  JobPortal Web:   http://localhost:8093 (new)"
echo ""

# Step 2: Create database
echo -e "${YELLOW}Step 1: Creating database...${NC}"
echo ""
read -sp "Enter MySQL root password: " ROOT_PASS
echo ""

echo "Creating database and user..."

docker exec -i flintgo-mysql mysql -u root -p"$ROOT_PASS" << 'EOFMYSQL' 2>/dev/null || {
    echo -e "${RED}❌ Failed to create database. Please check root password.${NC}"
    exit 1
}
CREATE DATABASE IF NOT EXISTS job_portal;
CREATE USER IF NOT EXISTS 'jobportal_user'@'%' IDENTIFIED BY 'jobportal_pass';
GRANT ALL PRIVILEGES ON job_portal.* TO 'jobportal_user'@'%';
FLUSH PRIVILEGES;
EOFMYSQL

echo -e "${GREEN}✅ Database created!${NC}"
echo ""

# Step 3: Import database
if [ -f "database_complete.sql" ]; then
    echo -e "${YELLOW}Step 2: Importing database schema...${NC}"
    docker exec -i flintgo-mysql mysql -u jobportal_user -pjobportal_pass job_portal < database_complete.sql
    echo -e "${GREEN}✅ Database imported!${NC}"
else
    echo -e "${YELLOW}⚠️  database_complete.sql not found. Skipping import.${NC}"
fi
echo ""

# Step 4: Copy docker-compose
echo -e "${YELLOW}Step 3: Setting up docker-compose.yml...${NC}"
cp docker-compose-fixed.yml docker-compose.yml
echo -e "${GREEN}✅ docker-compose.yml configured!${NC}"
echo ""

# Step 5: Copy database config
echo -e "${YELLOW}Step 4: Updating config/database.php...${NC}"
cp database-fixed.php config/database.php
echo -e "${GREEN}✅ config/database.php updated!${NC}"
echo ""

# Step 6: Build
echo -e "${YELLOW}Step 5: Building Docker container...${NC}"
docker compose build
echo -e "${GREEN}✅ Build complete!${NC}"
echo ""

# Step 7: Start
echo -e "${YELLOW}Step 6: Starting container...${NC}"
docker compose up -d
echo ""

# Step 8: Wait
echo "⏳ Waiting for services to start..."
sleep 5

# Step 9: Check status
echo ""
echo -e "${YELLOW}Step 7: Checking status...${NC}"
docker compose ps
echo ""

# Step 10: Test connection
echo -e "${YELLOW}Step 8: Testing database connection...${NC}"
sleep 2

if docker compose exec -T web php -r "
\$conn = @mysqli_connect('flintgo-mysql', 'jobportal_user', 'jobportal_pass', 'job_portal', 3306);
if (\$conn) {
    echo '✅ Connection successful!';
    mysqli_close(\$conn);
    exit(0);
} else {
    echo '❌ Connection failed: ' . mysqli_connect_error();
    exit(1);
}
" 2>/dev/null; then
    echo ""
    echo -e "${GREEN}Database connection: OK${NC}"
else
    echo ""
    echo -e "${RED}Database connection: FAILED${NC}"
    echo "Check logs: docker compose logs web"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "🌐 Access URLs:"
echo "   JobPortal:   http://localhost:8093"
echo "   phpMyAdmin:  http://localhost:8091"
echo ""
echo "📊 Database Info:"
echo "   Host:     flintgo-mysql (internal)"
echo "   Port:     3306 (internal) / 3307 (external)"
echo "   Database: job_portal"
echo "   User:     jobportal_user"
echo "   Password: jobportal_pass"
echo ""
echo "👤 Default Login:"
echo "   Email:    admin@jobportal.com"
echo "   Password: password"
echo ""
echo "🔧 Useful Commands:"
echo "   View logs:   docker compose logs -f web"
echo "   Restart:     docker compose restart web"
echo "   Stop:        docker compose down"
echo ""
echo -e "${GREEN}🎉 JobPortal is ready!${NC}"
echo ""
