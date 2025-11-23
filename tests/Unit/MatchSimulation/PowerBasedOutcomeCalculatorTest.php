<?php

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\Outcome\PowerBasedOutcomeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates probabilities correctly when home team is stronger', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 80.0,
        awayEffectivePower: 40.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    expect($probabilities)->toHaveKey(MatchOutcomeEnum::HOME->value)
        ->and($probabilities)->toHaveKey(MatchOutcomeEnum::DRAW->value)
        ->and($probabilities)->toHaveKey(MatchOutcomeEnum::AWAY->value)
        ->and($probabilities[MatchOutcomeEnum::HOME->value])->toBeGreaterThan($probabilities[MatchOutcomeEnum::AWAY->value])
        ->and(abs(array_sum($probabilities) - 1.0))->toBeLessThan(0.0001);
});

test('it calculates probabilities correctly when away team is stronger', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 40.0,
        awayEffectivePower: 80.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    expect($probabilities[MatchOutcomeEnum::AWAY->value])->toBeGreaterThan($probabilities[MatchOutcomeEnum::HOME->value])
        ->and(abs(array_sum($probabilities) - 1.0))->toBeLessThan(0.0001);
});

test('it calculates equal probabilities when teams have equal power', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 60.0,
        awayEffectivePower: 60.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    expect(abs($probabilities[MatchOutcomeEnum::HOME->value] - $probabilities[MatchOutcomeEnum::AWAY->value]))->toBeLessThan(0.0001)
        ->and(abs(array_sum($probabilities) - 1.0))->toBeLessThan(0.0001);
});

test('it includes draw probability in calculations', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    expect($probabilities[MatchOutcomeEnum::DRAW->value])->toBe(0.20)
        ->and(abs(array_sum($probabilities) - 1.0))->toBeLessThan(0.0001);
});

test('it handles zero total power gracefully', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 0.0,
        awayEffectivePower: 0.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    expect(abs($probabilities[MatchOutcomeEnum::HOME->value] - 0.33))->toBeLessThan(0.01)
        ->and(abs($probabilities[MatchOutcomeEnum::DRAW->value] - 0.34))->toBeLessThan(0.01)
        ->and(abs($probabilities[MatchOutcomeEnum::AWAY->value] - 0.33))->toBeLessThan(0.01)
        ->and(abs(array_sum($probabilities) - 1.0))->toBeLessThan(0.0001);
});

test('it handles negative power values', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: -10.0,
        awayEffectivePower: -5.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    // Should return equal probabilities when total power <= 0
    expect(abs($probabilities[MatchOutcomeEnum::HOME->value] - 0.33))->toBeLessThan(0.01)
        ->and(abs($probabilities[MatchOutcomeEnum::DRAW->value] - 0.34))->toBeLessThan(0.01)
        ->and(abs($probabilities[MatchOutcomeEnum::AWAY->value] - 0.33))->toBeLessThan(0.01);
});

test('it distributes non-draw probability proportionally', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 75.0,
        awayEffectivePower: 25.0,
        week: 1,
    );

    $calculator = new PowerBasedOutcomeCalculator();
    $probabilities = $calculator->calculateProbabilities($context);

    // Draw probability = 0.20, non-draw = 0.80
    // Home power ratio = 75 / 100 = 0.75
    // Away power ratio = 25 / 100 = 0.25
    // Expected: Home = 0.80 * 0.75 = 0.60, Away = 0.80 * 0.25 = 0.20

    expect(abs($probabilities[MatchOutcomeEnum::HOME->value] - 0.60))->toBeLessThan(0.0001)
        ->and(abs($probabilities[MatchOutcomeEnum::AWAY->value] - 0.20))->toBeLessThan(0.0001)
        ->and($probabilities[MatchOutcomeEnum::DRAW->value])->toBe(0.20);
});
