<?php

use App\Data\League\TeamStatsData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates goal difference correctly', function () {
    $stats = new TeamStatsData(
        goalsFor: 10,
        goalsAgainst: 5,
    );

    expect($stats->getGoalDifference())->toBe(5);
});

test('it calculates negative goal difference correctly', function () {
    $stats = new TeamStatsData(
        goalsFor: 3,
        goalsAgainst: 8,
    );

    expect($stats->getGoalDifference())->toBe(-5);
});

test('it calculates points correctly: 3 for win, 1 for draw', function () {
    $stats = new TeamStatsData(
        won: 2,
        drawn: 1,
        lost: 1,
    );

    expect($stats->getPoints())->toBe(7); // 2 wins * 3 + 1 draw * 1 = 7
});

test('it calculates zero points when no wins or draws', function () {
    $stats = new TeamStatsData(
        won: 0,
        drawn: 0,
        lost: 3,
    );

    expect($stats->getPoints())->toBe(0);
});

test('it increments played count', function () {
    $stats = new TeamStatsData(played: 5);
    $newStats = $stats->incrementPlayed();

    expect($newStats->played)->toBe(6)
        ->and($newStats->won)->toBe(0)
        ->and($newStats->drawn)->toBe(0)
        ->and($newStats->lost)->toBe(0);
});

test('it adds goals correctly', function () {
    $stats = new TeamStatsData(
        goalsFor: 5,
        goalsAgainst: 3,
    );
    $newStats = $stats->addGoals(2, 1);

    expect($newStats->goalsFor)->toBe(7)
        ->and($newStats->goalsAgainst)->toBe(4);
});

test('it increments won count', function () {
    $stats = new TeamStatsData(won: 3);
    $newStats = $stats->incrementWon();

    expect($newStats->won)->toBe(4)
        ->and($stats->won)->toBe(3); // Original unchanged (immutable)
});

test('it increments drawn count', function () {
    $stats = new TeamStatsData(drawn: 2);
    $newStats = $stats->incrementDrawn();

    expect($newStats->drawn)->toBe(3)
        ->and($stats->drawn)->toBe(2); // Original unchanged
});

test('it increments lost count', function () {
    $stats = new TeamStatsData(lost: 1);
    $newStats = $stats->incrementLost();

    expect($newStats->lost)->toBe(2)
        ->and($stats->lost)->toBe(1); // Original unchanged
});

test('it maintains immutability when chaining operations', function () {
    $stats = new TeamStatsData();
    $newStats = $stats
        ->incrementPlayed()
        ->addGoals(2, 1)
        ->incrementWon();

    expect($newStats->played)->toBe(1)
        ->and($newStats->goalsFor)->toBe(2)
        ->and($newStats->goalsAgainst)->toBe(1)
        ->and($newStats->won)->toBe(1)
        ->and($stats->played)->toBe(0)
        ->and($stats->goalsFor)->toBe(0)
        ->and($stats->won)->toBe(0);
});
