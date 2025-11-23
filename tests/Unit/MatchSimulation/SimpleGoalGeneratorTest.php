<?php

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\Goals\SimpleGoalGenerator;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;
use App\MatchSimulation\Random\FakeRandomGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it generates draw score correctly', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 60.0,
        awayEffectivePower: 60.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.5);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::DRAW);

    expect($result)->toBeInstanceOf(MatchSimulationResult::class)
        ->and($result->homeGoals)->toBe($result->awayGoals)
        ->and($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->homeGoals)->toBeLessThanOrEqual(3);
});

test('it generates home win score correctly', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 80.0,
        awayEffectivePower: 40.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.5);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::HOME);

    expect($result)->toBeInstanceOf(MatchSimulationResult::class)
        ->and($result->homeGoals)->toBeGreaterThan($result->awayGoals)
        ->and($result->homeGoals)->toBeGreaterThanOrEqual(1)
        ->and($result->homeGoals)->toBeLessThanOrEqual(4)
        ->and($result->awayGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->awayGoals)->toBeLessThan($result->homeGoals);
});

test('it generates away win score correctly', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 40.0,
        awayEffectivePower: 80.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.5);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::AWAY);

    expect($result)->toBeInstanceOf(MatchSimulationResult::class)
        ->and($result->awayGoals)->toBeGreaterThan($result->homeGoals)
        ->and($result->awayGoals)->toBeGreaterThanOrEqual(1)
        ->and($result->awayGoals)->toBeLessThanOrEqual(4)
        ->and($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->homeGoals)->toBeLessThan($result->awayGoals);
});

test('it generates scores within valid range for draw', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 60.0,
        awayEffectivePower: 60.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.0);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::DRAW);

    expect($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->homeGoals)->toBeLessThanOrEqual(3)
        ->and($result->awayGoals)->toBe($result->homeGoals);
});

test('it generates scores within valid range for home win', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 80.0,
        awayEffectivePower: 40.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.0);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::HOME);

    expect($result->homeGoals)->toBeGreaterThanOrEqual(1)
        ->and($result->homeGoals)->toBeLessThanOrEqual(4)
        ->and($result->awayGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->awayGoals)->toBeLessThan($result->homeGoals);
});

test('it generates scores within valid range for away win', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 40.0,
        awayEffectivePower: 80.0,
        week: 1,
    );

    $randomGenerator = new FakeRandomGenerator(0.0);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::AWAY);

    expect($result->awayGoals)->toBeGreaterThanOrEqual(1)
        ->and($result->awayGoals)->toBeLessThanOrEqual(4)
        ->and($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->homeGoals)->toBeLessThan($result->awayGoals);
});

test('it handles edge case when min is greater than max', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 80.0,
        awayEffectivePower: 40.0,
        week: 1,
    );

    // Use a random value that would cause min > max scenario
    $randomGenerator = new FakeRandomGenerator(1.0);
    $generator = new SimpleGoalGenerator($randomGenerator);

    $result = $generator->generateScore($context, MatchOutcomeEnum::HOME);

    // Should still return valid result
    expect($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->awayGoals)->toBeGreaterThanOrEqual(0);
});
