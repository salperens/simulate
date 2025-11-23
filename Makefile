.PHONY: help build up down restart logs shell bash shell-root bash-root composer install migrate seed test clean setup-ssl setup-full setup-hosts remove-hosts npm npm-install npm-dev npm-build check-npm

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker images
	docker-compose build --no-cache

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## Show logs from all containers
	docker-compose logs -f

logs-app: ## Show logs from app container
	docker-compose logs -f app

logs-nginx: ## Show logs from nginx container
	docker-compose logs -f nginx

logs-mysql: ## Show logs from mysql container
	docker-compose logs -f mysql

shell: ## Open shell in app container
	docker-compose exec app sh

bash: ## Open bash shell in app container
	docker-compose exec app bash

shell-root: ## Open shell as root in app container
	docker-compose exec -u root app sh

bash-root: ## Open bash shell as root in app container
	docker-compose exec -u root app bash

composer: ## Run composer command (usage: make composer CMD="install")
	docker-compose exec app composer $(CMD)

install: ## Install PHP dependencies
	docker-compose exec app composer install

update: ## Update PHP dependencies
	docker-compose exec app composer update

artisan: ## Run artisan command (usage: make artisan CMD="migrate")
	docker-compose exec app php artisan $(CMD)

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

key: ## Generate application key
	docker-compose exec app php artisan key:generate

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

cache-config: ## Cache configuration
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

setup: ## Initial setup (install dependencies, generate key, migrate, npm-install) - requires containers to be running
	docker-compose exec app composer install
	@if command -v npm > /dev/null; then \
		$(MAKE) npm-install; \
	else \
		echo "⚠️  npm not found. Skipping npm install. Install Node.js to enable frontend features."; \
	fi
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate

clean: ## Remove all containers, volumes and images
	docker-compose down -v --rmi all

ps: ## Show running containers
	docker-compose ps

stats: ## Show container resource usage
	docker stats

mysql: ## Connect to MySQL
	docker-compose exec mysql mysql -u lig_user -ppassword lig_simulation

mysql-root: ## Connect to MySQL as root
	docker-compose exec mysql mysql -u root -proot

opcache-status: ## Check OPcache status
	docker-compose exec app php -r "var_dump(opcache_get_status());"

phpinfo: ## Show PHP configuration
	docker-compose exec app php -i | grep -i opcache

setup-hosts: ## Add lig-simulation.local to hosts file (requires sudo)
	@echo "Adding lig-simulation.local to /etc/hosts..."
	@if grep -q "lig-simulation.local" /etc/hosts; then \
		echo "lig-simulation.local already exists in /etc/hosts"; \
	else \
		echo "127.0.0.1    lig-simulation.local" | sudo tee -a /etc/hosts > /dev/null; \
		echo "lig-simulation.local added to /etc/hosts"; \
	fi

remove-hosts: ## Remove lig-simulation.local from hosts file (requires sudo)
	@echo "Removing lig-simulation.local from /etc/hosts..."
	@sudo sed -i '' '/lig-simulation.local/d' /etc/hosts 2>/dev/null || sudo sed -i '/lig-simulation.local/d' /etc/hosts 2>/dev/null || true
	@echo "lig-simulation.local removed from /etc/hosts"

setup-ssl: ## Generate SSL certificates using mkcert
	@echo "🔐 Setting up SSL certificates..."
	@if ! command -v mkcert > /dev/null; then \
		echo "mkcert is not installed. Installing via Homebrew..."; \
		brew install mkcert || (echo "Please install mkcert manually: brew install mkcert" && exit 1); \
	fi
	@if [ ! -f "$(HOME)/Library/Application Support/mkcert/rootCA.pem" ] && [ ! -f "$(HOME)/.local/share/mkcert/rootCA.pem" ]; then \
		echo "Installing local CA (requires sudo)..."; \
		mkcert -install || (echo "Failed to install local CA. Please run: mkcert -install" && exit 1); \
	fi
	@mkdir -p docker/nginx/ssl
	@if [ -f docker/nginx/ssl/lig-simulation.local.crt ] && [ -f docker/nginx/ssl/lig-simulation.local.key ]; then \
		echo "SSL certificates already exist"; \
	else \
		echo "Generating SSL certificates..."; \
		cd docker/nginx/ssl && \
		mkcert lig-simulation.local "*.lig-simulation.local" localhost 127.0.0.1 ::1 && \
		if [ -f lig-simulation.local+4.pem ]; then \
			mv lig-simulation.local+4.pem lig-simulation.local.crt && \
			mv lig-simulation.local+4-key.pem lig-simulation.local.key; \
		fi && \
		echo "SSL certificates generated successfully"; \
	fi

check-npm: ## Check if npm is installed
	@if ! command -v npm > /dev/null; then \
		echo "npm is not installed."; \
		echo ""; \
		echo "Please install Node.js and npm:"; \
		echo "  macOS: brew install node"; \
		echo "  Linux: sudo apt-get install nodejs npm"; \
		echo "  Or visit: https://nodejs.org/"; \
		echo ""; \
		exit 1; \
	fi

npm-install: check-npm ## Install npm dependencies
	@echo "Installing npm dependencies..."
	npm install

npm-dev: check-npm ## Start Vite development server
	@echo "Starting Vite development server..."
	npm run dev

npm-build: check-npm ## Build assets for production
	@echo "Building assets for production..."
	npm run build

npm: check-npm ## Run npm command (usage: make npm CMD="install")
	npm $(CMD)

setup-full: ## Complete setup (hosts, SSL, build, up, install, key, migrate, npm-install)
	@echo "Starting full setup..."
	@$(MAKE) setup-hosts
	@$(MAKE) setup-ssl
	@echo "Building Docker images..."
	@$(MAKE) build
	@echo "Starting containers..."
	@$(MAKE) up
	@echo "Waiting for services to be ready..."
	@sleep 10
	@echo "Installing PHP dependencies..."
	@$(MAKE) install
	@if command -v npm > /dev/null; then \
		echo "Installing npm dependencies..."; \
		$(MAKE) npm-install; \
	else \
		echo "npm not found. Skipping npm install."; \
		echo "Install Node.js to enable frontend features: brew install node"; \
	fi
	@echo "Generating application key..."
	@$(MAKE) key
	@echo "Running migrations..."
	@$(MAKE) migrate
	@echo ""
	@echo "Setup complete! Access your application at:"
	@echo "https://lig-simulation.local/"
	@echo ""
	@if command -v npm > /dev/null; then \
		echo "To start the frontend development server, run:"; \
		echo "make npm-dev"; \
	fi

