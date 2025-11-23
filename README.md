# Lig Simulation

A football league simulation application built with Laravel and Vue.js.

🌐 **Live Demo**: [https://simulation.alperensarikaya.com/](https://simulation.alperensarikaya.com/)

## Features

- **Season Management**: Create, start, and complete seasons with flexible team count (minimum 2 teams, supports 4-team leagues as specified in requirements)
- **Team Management**: Manage teams with power ratings and attributes
- **Match Simulation**: Simulate matches with realistic outcomes based on team power, goalkeeper factor, supporter strength, and home advantage
- **Standings Calculation**: Automatic standings calculation with points (3 for win, 1 for draw), goal difference, and goals for
- **Championship Predictions**: Monte Carlo simulation for championship predictions (available in last 3 weeks)
- **Match Result Editing**: Edit match results and recalculate standings (disabled for completed seasons)
- **RESTful API**: Complete API for all operations
- **Test Coverage**: Comprehensive test suite with 92.1% code coverage

## Requirements

- Docker and Docker Compose
- Node.js and npm (for frontend development)
- Make (optional, but recommended for easier setup)

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/salperens/simulate.git
cd simulate
```

### 2. Run full setup (recommended)

This will set up everything automatically:

```bash
make setup-full
```

This command will:
- Add `lig-simulation.local` to your hosts file
- Generate SSL certificates
- Build Docker images
- Start containers
- Install PHP dependencies
- Install npm dependencies
- Generate application key
- Run database migrations

### 3. Access the application

- **Production**: [https://simulation.alperensarikaya.com/](https://simulation.alperensarikaya.com/)
- **Local HTTPS**: https://lig-simulation.local/
- **Local HTTP**: http://localhost:8000/

### 4. Start frontend development server (optional)

```bash
make npm-dev
```

## Manual Setup

If you prefer to set up manually:

### 1. Copy environment file

```bash
cp .env.example .env
```

### 2. Build and start containers

```bash
make build
make up
```

### 3. Install dependencies

```bash
make install
make npm-install
```

### 4. Generate application key

```bash
make key
```

### 5. Run migrations

```bash
make migrate
```

### 6. (Optional) Seed database

```bash
make seed
```

## Development

### Running Tests

```bash
# Run all tests
make test

# Run unit tests only
make test-unit

# Run feature tests only
make test-feature

# Run specific test
make test-filter FILTER="SimulateFixture"

# Run tests with coverage
make test-coverage

# Run tests in watch mode
make test-watch
```

### Common Commands

```bash
# View logs
make logs
make logs-app
make logs-nginx
make logs-mysql

# Access shell
make bash
make bash-root

# Run artisan commands
make artisan CMD="migrate"
make artisan CMD="tinker"

# Run composer commands
make composer CMD="require package/name"

# Clear caches
make cache-clear

# Cache configuration
make cache-config

# Connect to MySQL
make mysql
make mysql-root
```

### Frontend Development

```bash
# Install npm dependencies
make npm-install

# Start development server
make npm-dev

# Build for production
make npm-build
```

## Project Structure

```
app/
├── Actions/          # Business logic actions
├── Data/            # Data Transfer Objects (DTOs)
├── Enums/           # Enumerations
├── Exceptions/      # Custom exceptions and handlers
├── Http/            # Controllers, Requests, Resources
├── MatchSimulation/ # Match simulation logic
├── Models/          # Eloquent models
├── Prediction/      # Prediction algorithms
└── Providers/       # Service providers

tests/
├── Feature/         # Feature tests (API endpoints)
└── Unit/            # Unit tests (business logic)
```

## API Endpoints

### Seasons
- `GET /api/v1/seasons` - List all seasons
- `GET /api/v1/seasons/{id}` - Get season by ID
- `POST /api/v1/seasons` - Create new season
- `POST /api/v1/seasons/{id}/start` - Start season
- `POST /api/v1/seasons/{id}/complete` - Complete season
- `GET /api/v1/season/current` - Get current season

### Standings
- `GET /api/v1/standings` - Get standings (current season)
- `GET /api/v1/standings?season_id={id}` - Get standings for specific season
- `GET /api/v1/standings?week={week}` - Get standings up to specific week
- `GET /api/v1/standings?season_id={id}&week={week}` - Get standings for season up to week

### Fixtures
- `GET /api/v1/fixtures/week/{week}` - Get fixtures for week (optional: `?season_id={id}`)
- `PUT /api/v1/fixtures/{id}` - Update fixture result (body: `home_score`, `away_score`)

### Predictions
- `GET /api/v1/predictions/week/{week}` - Get predictions for week
- `GET /api/v1/predictions/current` - Get predictions for current week

### Play
- `POST /api/v1/league/week/{week}/play` - Play specific week
- `POST /api/v1/league/play-all` - Play all remaining matches

### Teams
- `GET /api/v1/teams` - List all teams

## Testing

The project uses Pest PHP for testing with PCOV extension for code coverage. Test coverage includes:

- **Total Tests**: 213 tests (212 passed, 1 skipped)
- **Unit Tests**: 163 tests covering Actions, Data classes, Models, and business logic
- **Feature Tests**: 50 tests covering all API endpoints and error scenarios
- **Code Coverage**: 92.1% overall coverage

Run tests with:

```bash
make test
make test-coverage  # With coverage report
```

## Environment Variables

Key environment variables (see `.env.example` for full list):

- `APP_ENV`: Application environment (local, production)
- `APP_DEBUG`: Debug mode
- `DB_CONNECTION`: Database connection (mysql, sqlite)
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password

## Docker Services

- **app**: PHP-FPM application container (PHP 8.4 with PCOV extension)
- **nginx**: Web server
- **mysql**: MySQL 8.0 database

## Documentation

For detailed technical documentation, see the [docs](./docs/) directory:

- [Architecture](./docs/Architecture.md) - System architecture and design patterns
- [API Documentation](./docs/API.md) - Complete API reference
- [Database Schema](./docs/Database.md) - Database structure and relationships
- [Development Guide](./docs/Development.md) - Development setup and guidelines
- [Testing Guide](./docs/Testing.md) - Testing strategies and best practices
- [Deployment Guide](./docs/Deployment.md) - Production deployment instructions

## Troubleshooting

### Containers won't start

```bash
# Check container status
make ps

# View logs
make logs

# Rebuild containers
make build
```

### Database connection issues

```bash
# Check MySQL logs
make logs-mysql

# Connect to MySQL
make mysql

# Verify database exists
SHOW DATABASES;
```

### Permission issues

```bash
# Fix storage permissions
make bash-root
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### npm issues

```bash
# Check npm installation
make check-npm

# Reinstall dependencies
rm -rf node_modules package-lock.json
make npm-install
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
