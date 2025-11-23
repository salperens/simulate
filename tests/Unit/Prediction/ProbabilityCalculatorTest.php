<?php

use App\Data\League\TeamStandingData;
use App\Data\Prediction\TeamPredictionData;
use App\Models\Team;
use App\Prediction\Algorithms\MonteCarlo\ProbabilityCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates probability correctly', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

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
            points: 7,
        ),
    ]);

    $championCounts = [
        $team1->id => 60, // 60 out of 100 simulations
        $team2->id => 40, // 40 out of 100 simulations
    ];

    $calculator = new ProbabilityCalculator(100);
    $predictions = $calculator->calculate($standings, $championCounts);

    expect($predictions)->toHaveCount(2);

    $team1Prediction = $predictions->firstWhere('teamId', $team1->id);
    $team2Prediction = $predictions->firstWhere('teamId', $team2->id);

    expect($team1Prediction->winProbability)->toBe(60.0)
        ->and($team2Prediction->winProbability)->toBe(40.0);
});

test('it rounds probability to two decimal places', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();

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

    $championCounts = [
        $team1->id => 33, // 33 out of 100 = 33.0%
    ];

    $calculator = new ProbabilityCalculator(100);
    $predictions = $calculator->calculate($standings, $championCounts);

    $prediction = $predictions->first();
    expect($prediction->winProbability)->toBe(33.0);
});

test('it handles zero champion count', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();

    $standings = collect([
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 0,
            drawn: 0,
            lost: 5,
            goalsFor: 2,
            goalsAgainst: 15,
            goalDifference: -13,
            points: 0,
        ),
    ]);

    $championCounts = [
        $team1->id => 0, // Never won in simulations
    ];

    $calculator = new ProbabilityCalculator(1000);
    $predictions = $calculator->calculate($standings, $championCounts);

    $prediction = $predictions->first();
    expect($prediction->winProbability)->toBe(0.0);
});

test('it handles missing team in champion counts', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

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
            points: 7,
        ),
    ]);

    $championCounts = [
        $team1->id => 100, // Only team1 in counts
    ];

    $calculator = new ProbabilityCalculator(100);
    $predictions = $calculator->calculate($standings, $championCounts);

    $team1Prediction = $predictions->firstWhere('teamId', $team1->id);
    $team2Prediction = $predictions->firstWhere('teamId', $team2->id);

    expect($team1Prediction->winProbability)->toBe(100.0)
        ->and($team2Prediction->winProbability)->toBe(0.0);
});

test('it calculates probability with different simulation counts', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();

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

    $championCounts = [
        $team1->id => 500, // 500 out of 1000 simulations
    ];

    $calculator = new ProbabilityCalculator(1000);
    $predictions = $calculator->calculate($standings, $championCounts);

    $prediction = $predictions->first();
    expect($prediction->winProbability)->toBe(50.0);
});

test('it returns TeamPredictionData with correct structure', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Test Team']);

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

    $championCounts = [
        $team1->id => 75,
    ];

    $calculator = new ProbabilityCalculator(100);
    $predictions = $calculator->calculate($standings, $championCounts);

    $prediction = $predictions->first();

    expect($prediction)->toBeInstanceOf(\App\Data\Prediction\TeamPredictionData::class)
        ->and($prediction->teamId)->toBe($team1->id)
        ->and($prediction->teamName)->toBe('Test Team')
        ->and($prediction->winProbability)->toBe(75.0);
});
