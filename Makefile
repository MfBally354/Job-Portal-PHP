.PHONY: help build up down restart logs logs-web logs-db shell shell-db clean backup restore

# Default target
help:
	@echo "🐳 JobPortal Docker Commands"
	@echo ""
	@echo "Setup & Start:"
	@echo "  make build         - Build Docker containers"
	@echo "  make up            - Start containers"
	@echo "  make start         - Build and start containers"
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
	@echo ""
	@echo "Database:"
	@echo "  make backup        - Backup database"
	@echo "  make restore       - Restore database"
	@echo ""
	@echo "Maintenance:"
	@echo "  make clean         - Remove containers and volumes"
	@echo "  make rebuild       - Clean rebuild everything"
	@echo ""

# Build containers
build:
	@echo "🔨 Building Docker containers..."
	docker compose build

# Start containers
up:
	@echo "🚀 Starting containers..."
	docker compose up -d
	@echo "✅ Containers started!"
	@echo "   Web: http://localhost:8091"
	@echo "   phpMyAdmin: http://localhost:8092"

# Build and start
start: build up

# Stop containers
down:
	@echo "⏹️  Stopping containers..."
	docker compose down
	@echo "✅ Containers stopped!"

# Stop containers only (don't remove)
stop:
	@echo "⏸️  Stopping containers..."
	docker compose stop

# Restart containers
restart:
	@echo "🔄 Restarting containers..."
	docker compose restart
	@echo "✅ Containers restarted!"

# View logs
logs:
	docker compose logs -f

# View web logs
logs-web:
	docker compose logs -f web

# View database logs
logs-db:
	docker compose logs -f db

# Access web container shell
shell:
	@echo "🐚 Accessing web container..."
	docker compose exec web bash

# Access database shell
shell-db:
	@echo "🗄️  Accessing database..."
	docker compose exec db mysql -u root -p

# Backup database
backup:
	@echo "💾 Backing up database..."
	@mkdir -p backups
	docker compose exec db mysqldump -u root -proot_password job_portal > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "✅ Database backed up to backups/"

# Restore database
restore:
	@echo "📥 Restoring database..."
	@read -p "Enter backup file path: " filepath; \
	docker compose exec -T db mysql -u root -proot_password job_portal < $$filepath
	@echo "✅ Database restored!"

# Clean everything
clean:
	@echo "🧹 Cleaning containers and volumes..."
	docker compose down -v
	@echo "✅ Cleaned!"

# Rebuild everything
rebuild: clean build up
	@echo "✨ Rebuild complete!"

# Show running containers
ps:
	docker compose ps

# Show container stats
stats:
	docker stats jobportal_web jobportal_db jobportal_phpmyadmin
