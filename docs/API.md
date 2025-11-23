# API Documentation

Complete API reference for the Lig Simulation application.

## Base URL

All API endpoints are prefixed with `/api/v1`.

## Authentication

Currently, the API does not require authentication. This may change in future versions.

## Response Format

All successful responses follow this structure:

```json
{
  "data": { ... }
}
```

Error responses follow this structure:

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Endpoints

### Seasons

#### List All Seasons

```http
GET /api/v1/seasons
```

Returns all seasons ordered by year (descending).

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "year": 2024,
      "name": "2024 Season",
      "status": "active",
      "start_date": "2024-01-01",
      "end_date": "2024-12-31",
      "current_week": 5,
      "total_weeks": 10
    }
  ]
}
```

#### Get Season by ID

```http
GET /api/v1/seasons/{id}
```

Returns a specific season by ID.

**Parameters:**
- `id` (path, required): Season ID

**Response:**
```json
{
  "data": {
    "id": 1,
    "year": 2024,
    "name": "2024 Season",
    "status": "active",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "current_week": 5,
    "total_weeks": 10
  }
}
```

**Errors:**
- `404`: Season not found

#### Get Current Season

```http
GET /api/v1/season/current
```

Returns the current active season (by current year).

**Response:**
```json
{
  "data": {
    "id": 1,
    "year": 2024,
    "name": "2024 Season",
    "status": "active",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "current_week": 5,
    "total_weeks": 10
  }
}
```

**Errors:**
- `404`: No active season found

#### Create Season

```http
POST /api/v1/seasons
```

Creates a new season.

**Request Body:**
```json
{
  "year": 2024,
  "name": "2024 Season",
  "team_ids": [1, 2, 3, 4]
}
```

**Parameters:**
- `year` (required): Season year
- `name` (optional): Season name (defaults to "{year} Season")
- `team_ids` (required, array): Array of team IDs to include in season

**Response:**
```json
{
  "data": {
    "id": 1,
    "year": 2024,
    "name": "2024 Season",
    "status": "draft",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "current_week": 1,
    "total_weeks": 6
  }
}
```

**Errors:**
- `422`: Validation error (missing teams, duplicate year, active season exists)

#### Start Season

```http
POST /api/v1/seasons/{id}/start
```

Starts a draft season, changing its status to active.

**Parameters:**
- `id` (path, required): Season ID

**Response:**
```json
{
  "data": {
    "id": 1,
    "year": 2024,
    "name": "2024 Season",
    "status": "active",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "current_week": 1,
    "total_weeks": 6
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Season is not in draft status
- `422`: Another active season already exists

#### Complete Season

```http
POST /api/v1/seasons/{id}/complete
```

Completes an active season, changing its status to completed.

**Parameters:**
- `id` (path, required): Season ID

**Response:**
```json
{
  "data": {
    "id": 1,
    "year": 2024,
    "name": "2024 Season",
    "status": "completed",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "current_week": 6,
    "total_weeks": 6
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Season is not active
- `422`: Not all matches have been played

### Standings

#### Get Standings

```http
GET /api/v1/standings
```

Returns league standings for the current season.

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)
- `week` (optional): Get standings up to specific week

**Examples:**
```http
GET /api/v1/standings
GET /api/v1/standings?season_id=1
GET /api/v1/standings?week=5
GET /api/v1/standings?season_id=1&week=5
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Team A",
      "played": 5,
      "won": 3,
      "drawn": 1,
      "lost": 1,
      "goals_for": 10,
      "goals_against": 5,
      "goal_difference": 5,
      "points": 10
    }
  ]
}
```

**Errors:**
- `404`: Season not found

### Fixtures

#### Get Fixtures by Week

```http
GET /api/v1/fixtures/week/{week}
```

Returns all fixtures for a specific week.

**Parameters:**
- `week` (path, required): Week number

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)

**Examples:**
```http
GET /api/v1/fixtures/week/1
GET /api/v1/fixtures/week/1?season_id=1
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "week_number": 1,
      "home_team": {
        "id": 1,
        "name": "Team A"
      },
      "away_team": {
        "id": 2,
        "name": "Team B"
      },
      "home_score": 2,
      "away_score": 1,
      "played_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

**Errors:**
- `404`: Season not found
- `422`: Invalid week number

#### Update Fixture Result

```http
PUT /api/v1/fixtures/{id}
```

Updates a fixture's score and recalculates standings and predictions.

**Parameters:**
- `id` (path, required): Fixture ID

**Request Body:**
```json
{
  "home_score": 2,
  "away_score": 1
}
```

**Parameters:**
- `home_score` (required, integer, min: 0): Home team score
- `away_score` (required, integer, min: 0): Away team score

**Response:**
```json
{
  "data": {
    "id": 1,
    "week_number": 1,
    "home_team": {
      "id": 1,
      "name": "Team A"
    },
    "away_team": {
      "id": 2,
      "name": "Team B"
    },
    "home_score": 2,
    "away_score": 1,
    "played_at": "2024-01-01T10:00:00Z"
  }
}
```

**Errors:**
- `404`: Fixture not found
- `422`: Validation error (invalid scores)

**Note:** This endpoint automatically:
- Updates the fixture result
- Recalculates standings for the season
- Recalculates predictions for affected weeks (if in prediction window)

### Predictions

#### Get Predictions by Week

```http
GET /api/v1/predictions/week/{week}
```

Returns championship predictions for a specific week.

**Parameters:**
- `week` (path, required): Week number

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)

**Examples:**
```http
GET /api/v1/predictions/week/8
GET /api/v1/predictions/week/8?season_id=1
```

**Response:**
```json
{
  "data": {
    "week": 8,
    "type": "championship",
    "simulations_run": 10000,
    "early_terminated": false,
    "predictions": [
      {
        "team_id": 1,
        "team_name": "Team A",
        "win_probability": 65.5
      }
    ]
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Predictions not available (week is not in last 3 weeks)

#### Get Current Week Predictions

```http
GET /api/v1/predictions/current
```

Returns championship predictions for the current week (if available).

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)

**Response:**
```json
{
  "data": {
    "week": 8,
    "type": "championship",
    "simulations_run": 10000,
    "early_terminated": false,
    "predictions": [
      {
        "team_id": 1,
        "team_name": "Team A",
        "win_probability": 65.5
      }
    ]
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Predictions not available (current week is not in last 3 weeks)

**Note:** Predictions are only available for the last 3 weeks of a season.

### Play

#### Play Week

```http
POST /api/v1/league/week/{week}/play
```

Simulates and plays all fixtures for a specific week.

**Parameters:**
- `week` (path, required): Week number to play

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)

**Response:**
```json
{
  "message": "Week 1 played successfully",
  "data": {
    "week": 1,
    "matches_played": 2
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Invalid week number
- `422`: Cannot play matches (season not active, week already played)

**Note:** This endpoint automatically:
- Simulates all fixtures for the week
- Updates fixture results
- Recalculates standings
- Calculates predictions (if week is in prediction window)

#### Play All Remaining Matches

```http
POST /api/v1/league/play-all
```

Simulates and plays all remaining unplayed fixtures for a season.

**Query Parameters:**
- `season_id` (optional): Specific season ID (defaults to current season)

**Response:**
```json
{
  "message": "All matches played successfully",
  "data": {
    "matches_played": 12
  }
}
```

**Errors:**
- `404`: Season not found
- `422`: Cannot play matches (season not active)

**Note:** This endpoint automatically:
- Simulates all remaining fixtures
- Updates fixture results
- Recalculates standings
- Calculates predictions for each week in prediction window

### Teams

#### List All Teams

```http
GET /api/v1/teams
```

Returns all teams in the system.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Team A",
      "power_rating": 75,
      "goalkeeper_factor": 1.10,
      "supporter_strength": 1.05,
      "home_advantage_multiplier": 1.15
    }
  ]
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 404 | Resource not found |
| 422 | Validation error or business rule violation |
| 500 | Internal server error |

## Rate Limiting

Currently, there is no rate limiting implemented. This may be added in future versions.

## Versioning

The API uses URL versioning (`/api/v1`). Future versions will be available at `/api/v2`, etc.

## Pagination

Currently, pagination is not implemented. All endpoints return complete result sets. Pagination may be added in future versions for large datasets.

