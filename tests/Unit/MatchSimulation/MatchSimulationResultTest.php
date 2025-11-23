<?php

use App\MatchSimulation\MatchSimulationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it correctly identifies draw result', function () {
    $result = new MatchSimulationResult(2, 2);

    expect($result->isDraw())->toBeTrue()
        ->and($result->homeWins())->toBeFalse()
        ->and($result->awayWins())->toBeFalse();
});

test('it correctly identifies home win', function () {
    $result = new MatchSimulationResult(3, 1);

    expect($result->isDraw())->toBeFalse()
        ->and($result->homeWins())->toBeTrue()
        ->and($result->awayWins())->toBeFalse();
});

test('it correctly identifies away win', function () {
    $result = new MatchSimulationResult(1, 3);

    expect($result->isDraw())->toBeFalse()
        ->and($result->homeWins())->toBeFalse()
        ->and($result->awayWins())->toBeTrue();
});

test('it returns null winner id for draw', function () {
    $result = new MatchSimulationResult(2, 2);

    expect($result->getWinnerId(1, 2))->toBeNull();
});

test('it returns home team id for home win', function () {
    $result = new MatchSimulationResult(3, 1);

    expect($result->getWinnerId(1, 2))->toBe(1);
});

test('it returns away team id for away win', function () {
    $result = new MatchSimulationResult(1, 3);

    expect($result->getWinnerId(1, 2))->toBe(2);
});

test('it handles zero-zero draw', function () {
    $result = new MatchSimulationResult(0, 0);

    expect($result->isDraw())->toBeTrue()
        ->and($result->getWinnerId(1, 2))->toBeNull();
});
