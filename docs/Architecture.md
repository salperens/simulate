# Architecture Documentation

This document describes the system architecture, design patterns, and architectural decisions for the Lig Simulation application.

## Overview

Lig Simulation is a football league simulation application built with Laravel (PHP) backend and Vue.js frontend. The application follows a clean architecture pattern with clear separation of concerns.

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.4
- **Database**: MySQL 8.0
- **Testing**: Pest PHP
- **Architecture Pattern**: Action-based architecture

### Frontend
- **Framework**: Vue.js 3 (Composition API)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **Icons**: Heroicons

### Infrastructure
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx
- **PHP Runtime**: PHP-FPM

## Architecture Patterns

### Action-Based Architecture

The application uses an Action-based architecture pattern where business logic is encapsulated in dedicated Action classes. This provides:

- **Single Responsibility**: Each action handles one specific operation
- **Testability**: Actions can be easily unit tested in isolation
- **Reusability**: Actions can be composed together
- **Dependency Injection**: Actions receive dependencies via constructor injection

**Example:**
```php
class UpdateFixtureAction
{
    public function __construct(
        private CalculateStandingsAction $calculateStandingsAction,
        private CalculatePredictionsIfApplicableAction $calculatePredictionsAction
    ) {}

    public function execute(int $fixtureId, int $homeScore, int $awayScore): Fixture
    {
        // Business logic here
    }
}
```

### Data Transfer Objects (DTOs)

DTOs are used to transfer data between layers and ensure type safety:

```php
class SeasonData
{
    public function __construct(
        public readonly int $id,
        public readonly int $year,
        public readonly ?string $name,
        public readonly SeasonStatusEnum $status,
        // ...
    ) {}
}
```

### Enum-Based Status Management

Status values are managed using PHP enums for type safety:

```php
enum SeasonStatusEnum: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
```

## Directory Structure

```
app/
├── Actions/              # Business logic actions
│   ├── Fixture/         # Fixture-related actions
│   ├── League/          # League operations (play, standings)
│   ├── Match/           # Match simulation actions
│   ├── Prediction/      # Prediction calculation actions
│   └── Season/          # Season management actions
├── Data/                 # Data Transfer Objects
│   ├── Fixture/
│   ├── League/
│   ├── Prediction/
│   └── Season/
├── Enums/                # Enumerations
│   ├── Match/
│   ├── Prediction/
│   └── Season/
├── Exceptions/           # Custom exceptions and handlers
│   ├── Handlers/        # Exception handlers
│   └── [Domain]/        # Domain-specific exceptions
├── Http/                 # HTTP layer
│   ├── Controllers/     # API controllers
│   ├── Requests/        # Form request validation
│   └── Resources/       # API resources (transformers)
├── MatchSimulation/      # Match simulation engine
│   ├── Contracts/       # Interfaces
│   ├── Goals/           # Goal generation logic
│   ├── Outcome/         # Outcome calculation
│   └── Random/          # Random number generation
├── Models/               # Eloquent models
├── Prediction/           # Prediction algorithms
│   └── Algorithms/      # Monte Carlo implementation
└── Providers/            # Service providers
```

## Key Components

### Match Simulation Engine

The match simulation engine uses a probabilistic approach based on team attributes:

1. **Power Calculation**: Combines team power rating with situational factors
2. **Outcome Probability**: Calculates win/draw/loss probabilities
3. **Goal Generation**: Generates realistic scorelines based on outcome
4. **Random Generation**: Uses configurable random generators for testing

**Components:**
- `DefaultMatchSimulator`: Main simulation orchestrator
- `PowerBasedOutcomeCalculator`: Calculates probabilities from team power
- `SimpleGoalGenerator`: Generates scores based on outcome
- `MatchContext`: Encapsulates match context (teams, attributes)

### Prediction System

The prediction system uses Monte Carlo simulation:

1. **Context Building**: Creates prediction context with current standings
2. **Fixture Simulation**: Simulates remaining fixtures multiple times
3. **Standings Calculation**: Calculates standings for each simulation
4. **Probability Calculation**: Determines win probability for each team
5. **Early Termination**: Optimizes by terminating when outcome is certain

**Components:**
- `MonteCarloPredictionAlgorithm`: Main algorithm implementation
- `FixtureSimulator`: Simulates remaining fixtures
- `StandingsCalculator`: Calculates standings from results
- `ProbabilityCalculator`: Calculates win probabilities
- `EarlyTerminationChecker`: Optimizes simulation performance
- `ChampionFinder`: Identifies champion from standings

### Exception Handling

Custom exception handling system with registered handlers:

```php
// Register exception handler
ExceptionHandlerRegistry::register(
    CannotPlayMatchesException::class,
    CannotPlayMatchesExceptionHandler::class
);
```

**Benefits:**
- Centralized error handling
- Consistent error responses
- Easy to extend with new exception types

## Data Flow

### Fixture Update Flow

```
1. PUT /api/v1/fixtures/{id}
   ↓
2. UpdateFixtureRequest (validation)
   ↓
3. UpdateFixtureAction
   ├── Update fixture scores
   ├── CalculateStandingsAction
   └── CalculatePredictionsIfApplicableAction
      └── (only if in prediction window)
   ↓
4. FixtureResource (transform)
   ↓
5. JSON Response
```

### Match Simulation Flow

```
1. POST /api/v1/league/week/{week}/play
   ↓
2. PlayWeekAction
   ├── Get fixtures for week
   ├── For each fixture:
   │   └── SimulateFixtureAction
   │       └── DefaultMatchSimulator
   │           ├── Calculate probabilities
   │           ├── Determine outcome
   │           └── Generate scores
   ├── CalculateStandingsAction
   └── CalculatePredictionsIfApplicableAction
   ↓
3. PlayWeekResource (transform)
   ↓
4. JSON Response
```

### Prediction Calculation Flow

```
1. CalculatePredictionsAction
   ↓
2. Build PredictionContext
   ├── Current standings
   ├── Remaining fixtures
   └── Season information
   ↓
3. MonteCarloPredictionAlgorithm
   ├── For each simulation (default: 10000):
   │   ├── EarlyTerminationChecker (optimization)
   │   ├── FixtureSimulator
   │   ├── StandingsCalculator
   │   └── ChampionFinder
   └── ProbabilityCalculator
   ↓
4. Save predictions to database
```

## Design Principles

### SOLID Principles

1. **Single Responsibility**: Each class has one reason to change
2. **Open/Closed**: Open for extension, closed for modification
3. **Liskov Substitution**: Subtypes are substitutable for their base types
4. **Interface Segregation**: Clients depend only on interfaces they use
5. **Dependency Inversion**: Depend on abstractions, not concretions

### DRY (Don't Repeat Yourself)

Business logic is centralized in Actions, avoiding duplication:
- Standings calculation is centralized in `CalculateStandingsAction`
- Prediction window checking is centralized in `CalculatePredictionsIfApplicableAction`

### Separation of Concerns

Clear separation between:
- **HTTP Layer**: Controllers, Requests, Resources
- **Business Logic**: Actions
- **Data Access**: Models
- **Domain Logic**: Match simulation, Prediction algorithms

## Dependency Injection

Laravel's service container handles dependency injection automatically:

```php
class FixtureController extends Controller
{
    public function __construct(
        private readonly UpdateFixtureAction $updateFixtureAction
    ) {}
}
```

## Service Providers

Custom service providers register bindings:

- `MatchSimulationServiceProvider`: Registers match simulation components
- `PredictionServiceProvider`: Registers prediction algorithm
- `ExceptionHandlerServiceProvider`: Registers exception handlers

## Testing Strategy

### Unit Tests
- Test Actions in isolation
- Mock dependencies
- Test business logic thoroughly

### Feature Tests
- Test API endpoints end-to-end
- Use database transactions
- Test error scenarios

### Test Coverage
- Target: 90%+ coverage
- Current: 92.1% coverage
- Focus on business logic, not framework code

## Performance Considerations

### Database Optimization
- Indexes on frequently queried columns
- Composite indexes for common query patterns
- Foreign key constraints for data integrity

### Prediction Optimization
- Early termination when outcome is certain
- Configurable simulation count
- Efficient standings calculation

### Caching Strategy
- Currently no caching implemented
- Future: Cache standings, predictions
- Future: Cache team data

## Security Considerations

### Input Validation
- Form Request validation for all inputs
- Type checking and constraints
- SQL injection prevention via Eloquent ORM

### Data Integrity
- Foreign key constraints
- Unique constraints
- Business rule validation

### Future Enhancements
- Authentication and authorization
- Rate limiting
- API key management

## Scalability

### Current Limitations
- Single database instance
- No horizontal scaling
- No queue system for long-running tasks

### Future Improvements
- Queue system for prediction calculations
- Redis for caching
- Database read replicas
- Horizontal scaling with load balancer

## Monitoring and Logging

### Logging
- Laravel's built-in logging system
- Logs stored in `storage/logs/laravel.log`
- Different log levels for different environments

### Future Enhancements
- Application performance monitoring (APM)
- Error tracking (Sentry, Bugsnag)
- Metrics collection
- Health check endpoints

