<?php

use App\Data\League\TeamStandingData;
use App\Data\Prediction\SimulatedFixtureData;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Prediction\Algorithms\MonteCarlo\StandingsCalculator;
use App\Prediction\PredictionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates standings from played and simulated fixtures', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Create played fixture: Team 1 wins 2-1
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    // Create unplayed fixture (will be simulated)
    /** @var Fixture $unplayedFixture */
    $unplayedFixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team1->id,
        'week_number'  => 2,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 1,
            won: 1,
            drawn: 0,
            lost: 0,
            goalsFor: 2,
            goalsAgainst: 1,
            goalDifference: 1,
            points: 3,
        ),
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 1,
            won: 0,
            drawn: 0,
            lost: 1,
            goalsFor: 1,
            goalsAgainst: 2,
            goalDifference: -1,
            points: 0,
        ),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 2,
        standings: $standings,
        remainingFixtures: collect([$unplayedFixture]),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    // Simulate: Team 1 wins 1-0
    $simulatedFixtures = collect([
        new SimulatedFixtureData(
            fixtureId: $unplayedFixture->id,
            homeScore: 0,
            awayScore: 1,
        ),
    ]);

    $calculator = new StandingsCalculator();
    $result = $calculator->calculate($context, $simulatedFixtures);

    expect($result)->toHaveCount(2);

    $team1Standing = $result->firstWhere('id', $team1->id);
    $team2Standing = $result->firstWhere('id', $team2->id);

    // Team 1: 2 matches, 2 wins, 3 goals for, 1 goal against
    expect($team1Standing->played)->toBe(2)
        ->and($team1Standing->won)->toBe(2)
        ->and($team1Standing->goalsFor)->toBe(3)
        ->and($team1Standing->goalsAgainst)->toBe(1)
        ->and($team1Standing->points)->toBe(6)
        ->and($team2Standing->played)->toBe(2)
        ->and($team2Standing->lost)->toBe(2)
        ->and($team2Standing->goalsFor)->toBe(1)
        ->and($team2Standing->goalsAgainst)->toBe(3)
        ->and($team2Standing->points)->toBe(0);

    // Team 2: 2 matches, 2 losses, 1 goal for, 3 goals against
});

test('it sorts standings correctly by points goal difference and goals for', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);
    /** @var Team $team3 */
    $team3 = Team::factory()->create(['name' => 'Team C']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id]);

    $standings = collect([
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 2,
            won: 1,
            drawn: 1,
            lost: 0,
            goalsFor: 5,
            goalsAgainst: 3,
            goalDifference: 2,
            points: 4, // Second place
        ),
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 2,
            won: 2,
            drawn: 0,
            lost: 0,
            goalsFor: 6,
            goalsAgainst: 2,
            goalDifference: 4,
            points: 6, // First place
        ),
        new TeamStandingData(
            id: $team3->id,
            name: $team3->name,
            played: 2,
            won: 1,
            drawn: 1,
            lost: 0,
            goalsFor: 4,
            goalsAgainst: 3,
            goalDifference: 1,
            points: 4, // Third place (same points, worse goal difference)
        ),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: $standings,
        remainingFixtures: collect(),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $calculator = new StandingsCalculator();
    $result = $calculator->calculate($context, collect());

    expect($result->first()->id)->toBe($team1->id) // Highest points
        ->and($result->get(1)->id)->toBe($team2->id) // Same points, better goal difference
        ->and($result->get(2)->id)->toBe($team3->id); // Same points, worse goal difference
});

test('it handles draw results correctly', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Create played draw fixture
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 1,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 1,
            won: 0,
            drawn: 1,
            lost: 0,
            goalsFor: 1,
            goalsAgainst: 1,
            goalDifference: 0,
            points: 1,
        ),
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 1,
            won: 0,
            drawn: 1,
            lost: 0,
            goalsFor: 1,
            goalsAgainst: 1,
            goalDifference: 0,
            points: 1,
        ),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: $standings,
        remainingFixtures: collect(),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $calculator = new StandingsCalculator();
    $result = $calculator->calculate($context, collect());

    $team1Standing = $result->firstWhere('id', $team1->id);
    $team2Standing = $result->firstWhere('id', $team2->id);

    expect($team1Standing->drawn)->toBe(1)
        ->and($team1Standing->points)->toBe(1)
        ->and($team2Standing->drawn)->toBe(1)
        ->and($team2Standing->points)->toBe(1);
});

test('it correctly calculates stats for home and away teams', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Team 1 as home: wins 3-1
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    // Team 1 as away: loses 0-2
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team1->id,
        'week_number'  => 2,
        'home_score'   => 2,
        'away_score'   => 0,
        'played_at'    => now(),
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 0,
            won: 0,
            drawn: 0,
            lost: 0,
            goalsFor: 0,
            goalsAgainst: 0,
            goalDifference: 0,
            points: 0,
        ),
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 0,
            won: 0,
            drawn: 0,
            lost: 0,
            goalsFor: 0,
            goalsAgainst: 0,
            goalDifference: 0,
            points: 0,
        ),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 2,
        standings: $standings,
        remainingFixtures: collect(),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $calculator = new StandingsCalculator();
    $result = $calculator->calculate($context, collect());

    $team1Standing = $result->firstWhere('id', $team1->id);
    $team2Standing = $result->firstWhere('id', $team2->id);

    // Team 1: 1 win, 1 loss, 3 goals for, 3 goals against
    expect($team1Standing->played)->toBe(2)
        ->and($team1Standing->won)->toBe(1)
        ->and($team1Standing->lost)->toBe(1)
        ->and($team1Standing->goalsFor)->toBe(3)
        ->and($team1Standing->goalsAgainst)->toBe(3)
        ->and($team1Standing->points)->toBe(3)
        ->and($team2Standing->played)->toBe(2)
        ->and($team2Standing->won)->toBe(1)
        ->and($team2Standing->lost)->toBe(1)
        ->and($team2Standing->goalsFor)->toBe(3)
        ->and($team2Standing->goalsAgainst)->toBe(3)
        ->and($team2Standing->points)->toBe(3);

    // Team 2: 1 win, 1 loss, 3 goals for, 3 goals against
});

