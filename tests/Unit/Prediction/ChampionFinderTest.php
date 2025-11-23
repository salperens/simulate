<?php

use App\Data\League\TeamStandingData;
use App\Models\Team;
use App\Prediction\Algorithms\MonteCarlo\ChampionFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it finds champion by points', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

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
            points: 9,
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
            points: 15, // Champion
        ),
    ]);

    $finder = new ChampionFinder();
    $champion = $finder->find($standings);

    expect($champion->id)->toBe($team1->id)
        ->and($champion->points)->toBe(15);
});

test('it finds champion by goal difference when points are equal', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $standings = collect([
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
            played: 5,
            won: 3,
            drawn: 1,
            lost: 1,
            goalsFor: 10,
            goalsAgainst: 8,
            goalDifference: 2,
            points: 10,
        ),
        new TeamStandingData(
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 3,
            drawn: 1,
            lost: 1,
            goalsFor: 15,
            goalsAgainst: 8,
            goalDifference: 7, // Better goal difference
            points: 10, // Same points
        ),
    ]);

    $finder = new ChampionFinder();
    $champion = $finder->find($standings);

    expect($champion->id)->toBe($team1->id)
        ->and($champion->goalDifference)->toBe(7);
});

test('it finds champion by goals for when points and goal difference are equal', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $standings = collect([
        new TeamStandingData(
            id: $team2->id,
            name: $team2->name,
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
            id: $team1->id,
            name: $team1->name,
            played: 5,
            won: 3,
            drawn: 1,
            lost: 1,
            goalsFor: 15, // More goals for
            goalsAgainst: 10,
            goalDifference: 5, // Same goal difference
            points: 10, // Same points
        ),
    ]);

    $finder = new ChampionFinder();
    $champion = $finder->find($standings);

    expect($champion->id)->toBe($team1->id)
        ->and($champion->goalsFor)->toBe(15);
});

test('it returns first team when all criteria are equal', function () {
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
            won: 3,
            drawn: 1,
            lost: 1,
            goalsFor: 10,
            goalsAgainst: 5,
            goalDifference: 5,
            points: 10, // All equal
        ),
    ]);

    $finder = new ChampionFinder();
    $champion = $finder->find($standings);

    // Should return first team when all criteria are equal
    expect($champion->id)->toBe($team1->id);
});
