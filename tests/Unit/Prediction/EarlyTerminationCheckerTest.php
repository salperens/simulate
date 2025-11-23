<?php

use App\Data\League\TeamStandingData;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use App\Prediction\Algorithms\MonteCarlo\EarlyTerminationChecker;
use App\Prediction\PredictionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure unique years for each test
    $this->testYear = now()->year + random_int(1000, 9999);
});

test('it returns false when standings are empty', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => $this->testYear,
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: collect(),
        remainingFixtures: collect(),
        type: \App\Enums\Prediction\PredictionTypeEnum::CHAMPIONSHIP,
    );

    $checker = new EarlyTerminationChecker();

    expect($checker->canTerminate($context))->toBeFalse();
});

test('it returns true when only one team exists', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => $this->testYear,
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 3,
            drawn: 1,
            lost: 1,
            goalsFor: 10,
            goalsAgainst: 5,
            goalDifference: 5,
            points: 10,
        ),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: $standings,
        remainingFixtures: collect(),
        type: \App\Enums\Prediction\PredictionTypeEnum::CHAMPIONSHIP,
    );

    $checker = new EarlyTerminationChecker();

    expect($checker->canTerminate($context))->toBeTrue();
});

test('it returns true when leader cannot be caught', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => $this->testYear,
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 5,
            drawn: 0,
            lost: 0,
            goalsFor: 15,
            goalsAgainst: 2,
            goalDifference: 13,
            points: 15, // Leader
        ),
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 5,
            won: 2,
            drawn: 1,
            lost: 2,
            goalsFor: 8,
            goalsAgainst: 10,
            goalDifference: -2,
            points: 7, // Second place
        ),
    ]);

    // Only 2 matches remaining (max 6 points possible)
    $remainingFixtures = collect([
        Fixture::factory()->make(['week_number' => 6]),
        Fixture::factory()->make(['week_number' => 6]),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 5,
        standings: $standings,
        remainingFixtures: $remainingFixtures,
        type: \App\Enums\Prediction\PredictionTypeEnum::CHAMPIONSHIP,
    );

    $checker = new EarlyTerminationChecker();

    // Leader has 15 points, second has 7 points (8 point difference)
    // Max possible points from 2 matches = 6
    // 8 > 6, so leader cannot be caught
    expect($checker->canTerminate($context))->toBeTrue();
});

test('it returns false when leader can still be caught', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => $this->testYear,
    ]);

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 5,
            drawn: 0,
            lost: 0,
            goalsFor: 15,
            goalsAgainst: 2,
            goalDifference: 13,
            points: 15, // Leader
        ),
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 5,
            won: 4,
            drawn: 0,
            lost: 1,
            goalsFor: 12,
            goalsAgainst: 8,
            goalDifference: 4,
            points: 12, // Second place
        ),
    ]);

    // 3 matches remaining (max 9 points possible)
    $remainingFixtures = collect([
        Fixture::factory()->make(['week_number' => 6]),
        Fixture::factory()->make(['week_number' => 6]),
        Fixture::factory()->make(['week_number' => 6]),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 5,
        standings: $standings,
        remainingFixtures: $remainingFixtures,
        type: \App\Enums\Prediction\PredictionTypeEnum::CHAMPIONSHIP,
    );

    $checker = new EarlyTerminationChecker();

    // Leader has 15 points, second has 12 points (3 point difference)
    // Max possible points from 3 matches = 9
    // 3 <= 9, so leader can still be caught
    expect($checker->canTerminate($context))->toBeFalse();
});

test('it sorts standings correctly before checking termination', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => $this->testYear,
    ]);

    // Create standings in wrong order
    $standings = collect([
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 5,
            won: 3,
            drawn: 0,
            lost: 2,
            goalsFor: 10,
            goalsAgainst: 8,
            goalDifference: 2,
            points: 9, // Second place
        ),
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 5,
            drawn: 0,
            lost: 0,
            goalsFor: 15,
            goalsAgainst: 2,
            goalDifference: 13,
            points: 15, // Leader (should be first)
        ),
        new TeamStandingData(
            id: $team3->id,
            name: $team3->name,
            played: 5,
            won: 1,
            drawn: 1,
            lost: 3,
            goalsFor: 5,
            goalsAgainst: 12,
            goalDifference: -7,
            points: 4, // Third place
        ),
    ]);

    $remainingFixtures = collect([
        Fixture::factory()->make(['week_number' => 6]),
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 5,
        standings: $standings,
        remainingFixtures: $remainingFixtures,
        type: \App\Enums\Prediction\PredictionTypeEnum::CHAMPIONSHIP,
    );

    $checker = new EarlyTerminationChecker();

    // Leader has 15 points, second has 9 points (6 point difference)
    // Max possible points from 1 match = 3
    // 6 > 3, so leader cannot be caught
    expect($checker->canTerminate($context))->toBeTrue();
});
