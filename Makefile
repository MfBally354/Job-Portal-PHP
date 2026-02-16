.PHONY: help build up down restart logs logs-web logs-db shell shell-db clean backup restore

# Default target
help:
	@echo "🐳 JobPortal Docker Commands (64-bit)"
	@echo ""
	@echo "🚀 Quick Start:"
	@echo "  make start         - Build and start everything"
	@echo "  make quick         - Start without rebuild"
	@echo ""
	@echo "Setup & Build:"
	@echo "  make build         - Build Docker containers"
	@echo "  make up            - Start containers"
	@echo ""
	@echo "Control:"
	@echo "  make down          - Stop and remove containers"
	@echo "  make restart       - Restart containers"
	@echo "  make stop          - Stop containers only"
	@echo ""
	@echo "Logs:"
	@echo "  make logs          - View all logs"
	@echo "  make logs-web      - View web container logs"
	@echo "  make logs-db       - View database logs"
	@echo ""
	@echo "Access:"
	@echo "  make shell         - Access web container shell"
	@echo "  make shell-db      - Access database shell"
	@echo "  make mysql         - Access MySQL CLI"
	@echo ""
	@echo "Database:"
	@echo "  make db-import     - Import database from database_complete.sql"
	@echo "  make db-export     - Export database to backups/"
	@echo "  make db-reset      - Reset database (WARNING: deletes data)"
	@echo ""
	@echo "Maintenance:"
	@echo "  make clean         - Remove containers and volumes"
	@echo "  make rebuild       - Clean rebuild everything"
	@echo "  make update        - Pull latest images and rebuild"
	@echo ""
	@echo "Info:"
	@echo "  make ps            - Show running containers"
	@echo "  make stats         - Show container stats"
	@echo "  make ip            - Show access URLs"
	@echo ""

# Quick start
start: build up
	@echo "✅ JobPortal started!"
	@make ip

quick: up
	@echo "✅ JobPortal started!"
	@make ip

# Build containers
build:
	@echo "🔨 Building Docker containers..."
	docker compose build --no-cache
	@echo "✅ Build complete!"

# Start containers
up:
	@echo "🚀 Starting containers..."
	docker compose up -d
	@echo "⏳ Waiting for services to be ready..."
	@sleep 5
	@echo "✅ Containers started!"

# Stop and remove containers
down:
	@echo "⏹️  Stopping and removing containers..."
	docker compose down
	@echo "✅ Containers stopped and removed!"

# Stop containers only
stop:
	@echo "⏸️  Stopping containers..."
	docker compose stop
	@echo "✅ Containers stopped!"

# Restart containers
restart:
	@echo "🔄 Restarting containers..."
	docker compose restart
	@echo "✅ Containers restarted!"

# View logs
logs:
	docker compose logs -f

logs-web:
	docker compose logs -f web

logs-db:
	docker compose logs -f db

# Access container shells
shell:
	@echo "🐚 Accessing web container..."
	docker compose exec web bash

shell-db:
	@echo "🗄️  Accessing database container..."
	docker compose exec db bash

mysql:
	@echo "🗄️  Accessing MySQL CLI..."
	docker compose exec db mysql -u root -proot_password job_portal

# Database operations
db-import:
	@echo "📥 Importing database..."
	@if [ ! -f "database_complete.sql" ]; then \
		echo "❌ database_complete.sql not found!"; \
		exit 1; \
	fi
	docker compose exec -T db mysql -u root -proot_password job_portal < database_complete.sql
	@echo "✅ Database imported!"

db-export:
	@echo "💾 Exporting database..."
	@mkdir -p backups
	docker compose exec db mysqldump -u root -proot_password job_portal > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "✅ Database exported to backups/"

db-reset:
	@echo "⚠️  WARNING: This will delete all database data!"
	@read -p "Are you sure? (yes/no): " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		echo "🗑️  Resetting database..."; \
		docker compose exec db mysql -u root -proot_password -e "DROP DATABASE IF EXISTS job_portal; CREATE DATABASE job_portal;"; \
		make db-import; \
		echo "✅ Database reset complete!"; \
	else \
		echo "❌ Cancelled"; \
	fi

# Clean everything
clean:
	@echo "🧹 Cleaning containers and volumes..."
	docker compose down -v
	@echo "✅ Cleaned!"

# Rebuild everything
rebuild: clean build up
	@echo "✨ Rebuild complete!"

# Update images and rebuild
update:
	@echo "📦 Pulling latest images..."
	docker compose pull
	@echo "🔨 Rebuilding containers..."
	docker compose build --no-cache
	@echo "🚀 Restarting services..."
	docker compose up -d
	@echo "✅ Update complete!"

# Show running containers
ps:
	@echo "📊 Running containers:"
	@docker compose ps

# Show container stats
stats:
	@echo "📈 Container statistics:"
	@docker stats jobportal_web jobportal_db jobportal_phpmyadmin --no-stream

# Show access information
ip:
	@echo ""
	@echo "=========================================="
	@echo "🌐 Access URLs:"
	@echo "=========================================="
	@echo "Web:        http://localhost:8091"
	@if command -v hostname > /dev/null 2>&1; then \
		IP=$$(hostname -I | awk '{print $$1}'); \
		echo "Web (LAN):  http://$$IP:8091"; \
		echo ""; \
		echo "phpMyAdmin: http://localhost:8092"; \
		echo "phpMyAdmin: http://$$IP:8092"; \
	else \
		echo ""; \
		echo "phpMyAdmin: http://localhost:8092"; \
	fi
	@echo ""
	@echo "=========================================="
	@echo "👤 Default Login:"
	@echo "=========================================="
	@echo "Email:    admin@jobportal.com"
	@echo "Password: password"
	@echo ""
	@echo "⚠️  Change password after first login!"
	@echo ""

# Install dependencies (if needed)
install:
	@echo "📦 Installing dependencies..."
	docker compose exec web composer install
	@echo "✅ Dependencies installed!"

# Fix permissions
fix-permissions:
	@echo "🔧 Fixing file permissions..."
	docker compose exec web chown -R www-data:www-data /var/www/html
	docker compose exec web chmod -R 755 /var/www/html/assets/uploads
	@echo "✅ Permissions fixed!"
