# Testing Guide

This guide covers testing strategies, best practices, and how to write effective tests for the Lig Simulation application.

## Testing Framework

The project uses **Pest PHP** for testing, which provides a modern, expressive testing API built on top of PHPUnit.

### Why Pest?

- Clean, readable syntax
- Better error messages
- Built-in parallel testing support
- Excellent Laravel integration
- Easy to learn and use

## Test Structure

```
tests/
├── Feature/         # Feature tests (API endpoints, integration)
│   └── Api/        # API endpoint tests
└── Unit/           # Unit tests (business logic, isolated)
    ├── Actions/    # Action tests
    ├── Data/       # DTO tests
    ├── Models/     # Model tests
    └── ...
```

## Running Tests

### Basic Commands

```bash
# Run all tests
make test

# Run unit tests only
make test-unit

# Run feature tests only
make test-feature

# Run specific test file
docker-compose exec app ./vendor/bin/pest tests/Unit/Actions/UpdateFixtureActionTest.php

# Run tests matching filter
make test-filter FILTER="UpdateFixture"

# Run with coverage
make test-coverage

# Watch mode (runs tests on file changes)
make test-watch
```

### Coverage Report

```bash
# Generate coverage report
make test-coverage

# Generate HTML coverage report
docker-compose exec app ./vendor/bin/pest --coverage --coverage-html=coverage
```

Current coverage: **92.1%**

## Test Types

### Unit Tests

Unit tests test individual components in isolation, with dependencies mocked.

**Location:** `tests/Unit/`

**Example:**
```php
use App\Actions\Fixture\UpdateFixtureAction;
use App\Actions\League\CalculateStandingsAction;

test('it updates fixture scores', function () {
    $fixture = Fixture::factory()->create([
        'home_score' => null,
        'away_score' => null,
    ]);
    
    $calculateStandingsAction = Mockery::mock(CalculateStandingsAction::class);
    $calculateStandingsAction->shouldReceive('execute')->once();
    
    $action = new UpdateFixtureAction($calculateStandingsAction, ...);
    $result = $action->execute($fixture->id, 2, 1);
    
    expect($result->home_score)->toBe(2);
    expect($result->away_score)->toBe(1);
});
```

### Feature Tests

Feature tests test complete user flows, including API endpoints and database interactions.

**Location:** `tests/Feature/`

**Example:**
```php
test('it updates fixture via API', function () {
    $fixture = Fixture::factory()->create();
    
    $response = $this->putJson("/api/v1/fixtures/{$fixture->id}", [
        'home_score' => 2,
        'away_score' => 1,
    ]);
    
    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'home_score', 'away_score']]);
    
    expect($fixture->fresh()->home_score)->toBe(2);
});
```

## Writing Tests

### Test Structure

Follow the **Arrange-Act-Assert** pattern:

```php
test('it performs action correctly', function () {
    // Arrange: Set up test data and dependencies
    $season = Season::factory()->create();
    $fixture = Fixture::factory()->create(['season_id' => $season->id]);
    
    // Act: Execute the code under test
    $result = $this->action->execute($fixture->id, 2, 1);
    
    // Assert: Verify the results
    expect($result->home_score)->toBe(2);
    expect($result->away_score)->toBe(1);
});
```

### Test Naming

Use descriptive test names that explain what is being tested:

```php
// Good
test('it calculates standings correctly with win draw loss')
test('it throws exception when season is not active')
test('it returns false when leader can still be caught')

// Bad
test('test standings')
test('test exception')
test('test false')
```

### Using Factories

Use factories to create test data:

```php
test('it processes fixtures', function () {
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year' => 2024,
    ]);
    
    $fixtures = Fixture::factory()->count(5)->create([
        'season_id' => $season->id,
        'week_number' => 1,
    ]);
    
    // Test logic
});
```

### Testing Exceptions

Test that exceptions are thrown correctly:

```php
test('it throws exception when season is not active', function () {
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
    ]);
    
    expect(fn() => $this->action->execute($season->id))
        ->toThrow(CannotPlayMatchesException::class);
});
```

### Testing Database Changes

Use database assertions:

```php
test('it creates fixture in database', function () {
    $season = Season::factory()->create();
    
    $this->action->execute($season->id, 1, 2);
    
    $this->assertDatabaseHas('fixtures', [
        'season_id' => $season->id,
        'home_score' => 1,
        'away_score' => 2,
    ]);
});
```

## Testing Best Practices

### 1. Test One Thing

Each test should verify one specific behavior:

```php
// Good: Tests one thing
test('it calculates points correctly: 3 for win', function () {
    // Test win points
});

test('it calculates points correctly: 1 for draw', function () {
    // Test draw points
});

// Bad: Tests multiple things
test('it calculates points', function () {
    // Tests win, draw, and loss
});
```

### 2. Use Descriptive Assertions

Use Pest's expressive assertions:

```php
expect($result)->toBe(5);
expect($result)->toBeTrue();
expect($result)->toBeInstanceOf(Fixture::class);
expect($result)->toHaveCount(3);
expect($result)->toContain($item);
```

### 3. Avoid Test Interdependence

Tests should be independent and runnable in any order:

```php
// Good: Independent test
test('it creates season', function () {
    $season = Season::factory()->create();
    expect($season)->toBeInstanceOf(Season::class);
});

// Bad: Depends on previous test
test('it updates season', function () {
    // Assumes $season exists from previous test
    $season->update(['name' => 'New Name']);
});
```

### 4. Use setUp and tearDown

Use `beforeEach` and `afterEach` for common setup:

```php
beforeEach(function () {
    $this->season = Season::factory()->create();
    $this->action = new ExampleAction();
});

test('it performs action', function () {
    $result = $this->action->execute($this->season);
    // ...
});
```

### 5. Mock External Dependencies

Mock dependencies that are not under test:

```php
test('it calls dependency action', function () {
    $dependency = Mockery::mock(DependencyAction::class);
    $dependency->shouldReceive('execute')->once()->andReturn($result);
    
    $action = new ExampleAction($dependency);
    $action->execute($data);
});
```

## Testing Actions

Actions are the core business logic components. Test them thoroughly:

```php
test('it executes action successfully', function () {
    $action = new UpdateFixtureAction(
        Mockery::mock(CalculateStandingsAction::class),
        Mockery::mock(CalculatePredictionsIfApplicableAction::class)
    );
    
    $fixture = Fixture::factory()->create();
    $result = $action->execute($fixture->id, 2, 1);
    
    expect($result)->toBeInstanceOf(Fixture::class);
    expect($result->home_score)->toBe(2);
});

test('it throws exception on invalid input', function () {
    $action = new UpdateFixtureAction(...);
    
    expect(fn() => $action->execute(999, 2, 1))
        ->toThrow(ModelNotFoundException::class);
});
```

## Testing API Endpoints

Test complete API flows:

```php
test('it returns fixtures for week', function () {
    $season = Season::factory()->create();
    $fixtures = Fixture::factory()->count(3)->create([
        'season_id' => $season->id,
        'week_number' => 1,
    ]);
    
    $response = $this->getJson("/api/v1/fixtures/week/1?season_id={$season->id}");
    
    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('it returns 404 when season not found', function () {
    $response = $this->getJson('/api/v1/fixtures/week/1?season_id=999');
    
    $response->assertNotFound();
});
```

## Testing Edge Cases

Always test edge cases:

```php
test('it handles empty standings', function () {
    $season = Season::factory()->create();
    $standings = $this->action->execute($season);
    
    expect($standings)->toBeEmpty();
});

test('it handles zero goals', function () {
    $fixture = Fixture::factory()->create([
        'home_score' => 0,
        'away_score' => 0,
    ]);
    
    expect($fixture->isDraw())->toBeTrue();
});
```

## Performance Testing

For performance-critical code, test execution time:

```php
test('it calculates predictions quickly', function () {
    $start = microtime(true);
    
    $this->action->execute($season, $week);
    
    $duration = microtime(true) - $start;
    expect($duration)->toBeLessThan(5.0); // Should complete in under 5 seconds
});
```

## Continuous Integration

Tests run automatically in CI/CD:

```yaml
# Example GitHub Actions workflow
- name: Run tests
  run: make test-coverage
```

## Coverage Goals

- **Minimum**: 80% coverage
- **Target**: 90%+ coverage
- **Current**: 92.1% coverage

Focus coverage on:
- Business logic (Actions)
- Domain models
- Critical paths

Don't focus on:
- Framework code
- Simple getters/setters
- Generated code

## Common Issues and Solutions

### Issue: Tests are slow

**Solution:**
- Use database transactions (`RefreshDatabase`)
- Mock expensive operations
- Use factories efficiently
- Run tests in parallel

### Issue: Tests fail randomly

**Solution:**
- Ensure tests are independent
- Use unique data (random years, IDs)
- Clean up after tests
- Avoid shared state

### Issue: Database state issues

**Solution:**
- Use `RefreshDatabase` trait
- Use `DatabaseTransactions` for feature tests
- Create fresh data in each test

## Resources

- [Pest PHP Documentation](https://pestphp.com/docs)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

