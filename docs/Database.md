# Database Schema

This document describes the database structure and relationships for the Lig Simulation application.

## Tables

### teams

Stores team information and attributes used for match simulation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY | Team identifier |
| name | string | NOT NULL | Team name |
| power_rating | integer | DEFAULT 50 | Base power rating (0-100) |
| goalkeeper_factor | decimal(3) | DEFAULT 1.00 | Goalkeeper strength multiplier |
| supporter_strength | decimal(3) | DEFAULT 1.00 | Supporter influence factor |
| home_advantage_multiplier | decimal(3) | DEFAULT 1.10 | Home advantage multiplier |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- Primary key on `id`

### seasons

Represents a football season with its status and dates.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY | Season identifier |
| year | year | UNIQUE, NOT NULL | Season year (e.g., 2024) |
| name | string | NULLABLE | Season name |
| status | enum | DEFAULT 'draft' | Season status: draft, active, completed |
| start_date | date | NULLABLE | Season start date |
| end_date | date | NULLABLE | Season end date |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- Primary key on `id`
- Unique index on `year`

**Status Values:**
- `draft`: Season created but not started
- `active`: Season is currently running
- `completed`: Season has finished

### season_teams

Pivot table linking teams to seasons (many-to-many relationship).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY | Pivot record identifier |
| season_id | bigint | FOREIGN KEY | Reference to seasons.id |
| team_id | bigint | FOREIGN KEY | Reference to teams.id |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- Primary key on `id`
- Unique composite index on `(season_id, team_id)`
- Foreign key on `season_id` → `seasons.id` (CASCADE DELETE)
- Foreign key on `team_id` → `teams.id` (CASCADE DELETE)

### fixtures

Stores match fixtures with results.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY | Fixture identifier |
| season_id | bigint | FOREIGN KEY | Reference to seasons.id |
| week_number | integer | NOT NULL | Week number in season |
| home_team_id | bigint | FOREIGN KEY | Reference to teams.id |
| away_team_id | bigint | FOREIGN KEY | Reference to teams.id |
| home_score | integer | NULLABLE | Home team score |
| away_score | integer | NULLABLE | Away team score |
| played_at | timestamp | NULLABLE | When the match was played |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- Primary key on `id`
- Unique composite index on `(season_id, week_number, home_team_id, away_team_id)`
- Foreign key on `season_id` → `seasons.id` (CASCADE DELETE)
- Foreign key on `home_team_id` → `teams.id` (CASCADE DELETE)
- Foreign key on `away_team_id` → `teams.id` (CASCADE DELETE)

**Business Rules:**
- `home_score` and `away_score` are NULL until match is played
- `played_at` is NULL until match is played
- A team cannot play against itself (`home_team_id` ≠ `away_team_id`)

### championship_predictions

Stores Monte Carlo simulation results for championship predictions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY | Prediction identifier |
| season_id | bigint | FOREIGN KEY | Reference to seasons.id |
| week_number | integer | NOT NULL | Week number for prediction |
| team_id | bigint | FOREIGN KEY | Reference to teams.id |
| win_probability | decimal(5) | NOT NULL | Win probability percentage (0-100) |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- Primary key on `id`
- Unique composite index on `(season_id, week_number, team_id)`
- Composite index on `(season_id, week_number)` for efficient queries
- Foreign key on `season_id` → `seasons.id` (CASCADE DELETE)
- Foreign key on `team_id` → `teams.id` (CASCADE DELETE)

**Business Rules:**
- Predictions are only available for the last 3 weeks of a season
- Win probabilities for all teams in a week should sum to approximately 100%
- Predictions are recalculated when fixtures are updated

## Relationships

```
seasons (1) ──< (many) season_teams (many) >── (1) teams
seasons (1) ──< (many) fixtures
seasons (1) ──< (many) championship_predictions
teams (1) ──< (many) fixtures (as home_team)
teams (1) ──< (many) fixtures (as away_team)
teams (1) ──< (many) championship_predictions
```

## Entity Relationship Diagram

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   seasons   │         │ season_teams │         │    teams    │
├─────────────┤         ├──────────────┤         ├─────────────┤
│ id          │◄──┐     │ id           │     ┌──►│ id          │
│ year (UK)   │   │     │ season_id (FK)    │     │ name        │
│ name        │   │     │ team_id (FK)      │     │ power_rating│
│ status      │   │     └──────────────────┘     │ goalkeeper_ │
│ start_date  │   │                               │   factor    │
│ end_date    │   │                               │ supporter_  │
└─────────────┘   │                               │   strength  │
      │           │                               │ home_advant │
      │           │                               │   age_mult  │
      │           │                               └─────────────┘
      │           │
      │           │
      ▼           │
┌─────────────┐  │
│  fixtures   │  │
├─────────────┤  │
│ id          │  │
│ season_id(FK)──┘
│ week_number │
│ home_team_id(FK)──┐
│ away_team_id(FK)──┤
│ home_score  │     │
│ away_score  │     │
│ played_at   │     │
└─────────────┘     │
      │             │
      │             │
      ▼             │
┌─────────────────────┐
│championship_        │
│predictions          │
├─────────────────────┤
│ id                  │
│ season_id (FK)──────┘
│ week_number         │
│ team_id (FK)────────┘
│ win_probability     │
└─────────────────────┘
```

## Data Integrity

### Foreign Key Constraints

All foreign keys use `CASCADE DELETE` to maintain referential integrity:
- Deleting a season removes all related fixtures, season_teams, and predictions
- Deleting a team removes all related fixtures and predictions
- Deleting a season_team removes the team from that season

### Unique Constraints

- `seasons.year`: Ensures only one season per year
- `season_teams(season_id, team_id)`: Prevents duplicate team assignments
- `fixtures(season_id, week_number, home_team_id, away_team_id)`: Prevents duplicate fixtures
- `championship_predictions(season_id, week_number, team_id)`: Ensures one prediction per team per week

## Common Queries

### Get all fixtures for a season
```sql
SELECT * FROM fixtures WHERE season_id = ? ORDER BY week_number, id;
```

### Get standings for a season
```sql
SELECT 
    t.id,
    t.name,
    COUNT(f.id) as played,
    SUM(CASE WHEN (f.home_team_id = t.id AND f.home_score > f.away_score) 
             OR (f.away_team_id = t.id AND f.away_score > f.home_score) THEN 1 ELSE 0 END) as won,
    SUM(CASE WHEN f.home_score = f.away_score THEN 1 ELSE 0 END) as drawn,
    SUM(CASE WHEN (f.home_team_id = t.id AND f.home_score < f.away_score) 
             OR (f.away_team_id = t.id AND f.away_score < f.home_score) THEN 1 ELSE 0 END) as lost,
    SUM(CASE WHEN f.home_team_id = t.id THEN f.home_score ELSE f.away_score END) as goals_for,
    SUM(CASE WHEN f.home_team_id = t.id THEN f.away_score ELSE f.home_score END) as goals_against,
    SUM(CASE WHEN f.home_team_id = t.id THEN f.home_score - f.away_score 
             ELSE f.away_score - f.home_score END) as goal_difference,
    SUM(CASE WHEN (f.home_team_id = t.id AND f.home_score > f.away_score) 
             OR (f.away_team_id = t.id AND f.away_score > f.home_score) THEN 3 
             WHEN f.home_score = f.away_score THEN 1 ELSE 0 END) as points
FROM teams t
JOIN season_teams st ON st.team_id = t.id
LEFT JOIN fixtures f ON (f.home_team_id = t.id OR f.away_team_id = t.id) 
    AND f.season_id = st.season_id 
    AND f.played_at IS NOT NULL
WHERE st.season_id = ?
GROUP BY t.id, t.name
ORDER BY points DESC, goal_difference DESC, goals_for DESC;
```

### Get predictions for a week
```sql
SELECT * FROM championship_predictions 
WHERE season_id = ? AND week_number = ? 
ORDER BY win_probability DESC;
```

