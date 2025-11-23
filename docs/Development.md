# Development Guide

This guide provides information for developers working on the Lig Simulation project.

## Prerequisites

- Docker and Docker Compose
- Node.js 18+ and npm
- Git
- Make (optional, but recommended)

## Getting Started

### Initial Setup

1. Clone the repository:
```bash
git clone https://github.com/salperens/simulate.git
cd simulate
```

2. Run full setup:
```bash
make setup-full
```

This will:
- Set up hosts file
- Generate SSL certificates
- Build Docker images
- Start containers
- Install dependencies
- Run migrations

3. Access the application:
- HTTPS: https://lig-simulation.local/
- HTTP: http://localhost:8000/

## Development Workflow

### Backend Development

#### Running Tests

```bash
# Run all tests
make test

# Run unit tests only
make test-unit

# Run feature tests only
make test-feature

# Run specific test
make test-filter FILTER="UpdateFixture"

# Run with coverage
make test-coverage

# Watch mode
make test-watch
```

#### Code Style

The project follows PSR-12 coding standards. Use Laravel Pint or PHP CS Fixer:

```bash
# Using Laravel Pint (if installed)
docker-compose exec app ./vendor/bin/pint

# Or use PHP CS Fixer
docker-compose exec app ./vendor/bin/php-cs-fixer fix
```

#### Database Migrations

```bash
# Create migration
make artisan CMD="make:migration create_example_table"

# Run migrations
make migrate

# Rollback last migration
make artisan CMD="migrate:rollback"

# Fresh migration with seeding
make migrate-fresh
```

#### Database Seeding

```bash
# Run seeders
make seed

# Create seeder
make artisan CMD="make:seeder ExampleSeeder"
```

### Frontend Development

#### Development Server

```bash
# Start Vite dev server
make npm-dev
```

The dev server runs on `http://localhost:5173` with hot module replacement.

#### Building Assets

```bash
# Build for production
make npm-build
```

#### Code Style

The project uses ESLint for JavaScript/Vue code. Run:

```bash
npm run lint
```

## Project Structure

### Backend Structure

```
app/
├── Actions/          # Business logic (one action per operation)
├── Data/            # DTOs (Data Transfer Objects)
├── Enums/           # Enumerations
├── Exceptions/      # Custom exceptions and handlers
├── Http/            # HTTP layer (Controllers, Requests, Resources)
├── MatchSimulation/ # Match simulation engine
├── Models/          # Eloquent models
├── Prediction/      # Prediction algorithms
└── Providers/       # Service providers
```

### Frontend Structure

```
resources/js/
├── App.vue          # Main application component
├── components/      # Vue components
├── composables/     # Vue composables (reusable logic)
└── bootstrap.js     # Application bootstrap
```

## Coding Standards

### PHP

- Follow PSR-12 coding standard
- Use type hints for all parameters and return types
- Use readonly properties where appropriate
- Use enums for status values
- Keep classes focused (Single Responsibility Principle)

**Example:**
```php
final readonly class UpdateFixtureAction
{
    public function __construct(
        private CalculateStandingsAction $calculateStandingsAction
    ) {}

    public function execute(int $fixtureId, int $homeScore, int $awayScore): Fixture
    {
        // Implementation
    }
}
```

### JavaScript/Vue

- Use Composition API (not Options API)
- Use `<script setup>` syntax
- Use TypeScript-style JSDoc comments
- Follow Vue.js style guide
- Use Tailwind CSS for styling

**Example:**
```vue
<script setup>
import { ref } from 'vue'

const props = defineProps({
  match: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['update'])
</script>
```

## Creating New Features

### Adding a New Action

1. Create action class:
```bash
make artisan CMD="make:action Example/ExampleAction"
```

2. Implement the action:
```php
final readonly class ExampleAction
{
    public function __construct(
        private DependencyAction $dependencyAction
    ) {}

    public function execute(ExampleData $data): ResultData
    {
        // Business logic
    }
}
```

3. Create corresponding DTOs:
```bash
make artisan CMD="make:data Example/ExampleData"
```

4. Write tests:
```php
test('it performs example action', function () {
    $action = new ExampleAction(...);
    $result = $action->execute($data);
    
    expect($result)->toBeInstanceOf(ResultData::class);
});
```

### Adding a New API Endpoint

1. Create controller method:
```php
public function example(Request $request): JsonResponse
{
    $data = ExampleRequest::from($request);
    $result = $this->exampleAction->execute($data);
    
    return new ExampleResource($result);
}
```

2. Add route:
```php
Route::get('/example', [ExampleController::class, 'example']);
```

3. Create request validation:
```bash
make artisan CMD="make:request Api/V1/ExampleRequest"
```

4. Create resource:
```bash
make artisan CMD="make:resource Api/V1/ExampleResource"
```

5. Write feature test:
```php
test('it returns example data', function () {
    $response = $this->getJson('/api/v1/example');
    
    $response->assertOk()
        ->assertJsonStructure(['data']);
});
```

### Adding a New Vue Component

1. Create component file:
```vue
<!-- resources/js/components/ExampleComponent.vue -->
<template>
  <div class="example-component">
    <!-- Component markup -->
  </div>
</template>

<script setup>
defineProps({
  // Props
})

defineEmits(['event'])
</script>
```

2. Import and use in parent component:
```vue
<script setup>
import ExampleComponent from './components/ExampleComponent.vue'
</script>

<template>
  <ExampleComponent />
</template>
```

## Testing Guidelines

### Unit Tests

- Test one thing at a time
- Use descriptive test names
- Arrange-Act-Assert pattern
- Mock external dependencies

**Example:**
```php
test('it calculates standings correctly', function () {
    // Arrange
    $season = Season::factory()->create();
    $fixtures = Fixture::factory()->count(3)->create(['season_id' => $season->id]);
    
    // Act
    $standings = $this->action->execute($season);
    
    // Assert
    expect($standings)->toHaveCount(3);
});
```

### Feature Tests

- Test complete user flows
- Test error scenarios
- Use database transactions
- Test API responses

**Example:**
```php
test('it updates fixture and recalculates standings', function () {
    $fixture = Fixture::factory()->create();
    
    $response = $this->putJson("/api/v1/fixtures/{$fixture->id}", [
        'home_score' => 2,
        'away_score' => 1,
    ]);
    
    $response->assertOk();
    expect($fixture->fresh()->home_score)->toBe(2);
});
```

## Debugging

### Backend Debugging

```bash
# View logs
make logs-app

# Access container shell
make bash

# Use Laravel Tinker
make artisan CMD="tinker"
```

### Frontend Debugging

- Use browser DevTools
- Vue DevTools extension
- Console logging
- Network tab for API calls

## Common Tasks

### Database Operations

```bash
# Connect to MySQL
make mysql

# Run raw SQL
make artisan CMD="db:show"

# Check migrations status
make artisan CMD="migrate:status"
```

### Cache Management

```bash
# Clear all caches
make cache-clear

# Cache configuration
make cache-config
```

### Composer Operations

```bash
# Install package
make composer CMD="require vendor/package"

# Update dependencies
make update

# Autoload dump
make composer CMD="dump-autoload"
```

## Git Workflow

### Branch Naming

- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `refactor/refactor-description` - Refactoring
- `docs/documentation-update` - Documentation

### Commit Messages

Follow conventional commits:
- `feat: add fixture update functionality`
- `fix: resolve standings calculation bug`
- `refactor: extract prediction logic to action`
- `docs: update API documentation`

### Pull Requests

- Clear title and description
- Reference related issues
- Include test coverage
- Request review before merging

## Performance Tips

### Backend

- Use eager loading to avoid N+1 queries
- Add database indexes for frequently queried columns
- Cache expensive calculations
- Use database transactions for bulk operations

### Frontend

- Lazy load components when possible
- Optimize images
- Use computed properties for derived data
- Debounce/throttle API calls

## Troubleshooting

### Container Issues

```bash
# Rebuild containers
make build

# Restart containers
make restart

# View container logs
make logs
```

### Database Issues

```bash
# Reset database
make migrate-fresh

# Check database connection
make mysql
```

### Frontend Issues

```bash
# Clear node_modules
rm -rf node_modules package-lock.json
make npm-install

# Rebuild assets
make npm-build
```

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Pest PHP Documentation](https://pestphp.com/docs)
