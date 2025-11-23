<?php

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\Contracts\GoalGenerator;
use App\MatchSimulation\Contracts\OutcomeCalculator;
use App\MatchSimulation\DefaultMatchSimulator;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;
use App\MatchSimulation\Random\FakeRandomGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it simulates match and returns result', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $outcomeCalculator = new class implements OutcomeCalculator {
        public function calculateProbabilities(MatchContext $context): array
        {
            return [
                MatchOutcomeEnum::HOME->value => 0.5,
                MatchOutcomeEnum::DRAW->value => 0.2,
                MatchOutcomeEnum::AWAY->value => 0.3,
            ];
        }
    };

    $goalGenerator = new class implements GoalGenerator {
        public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
        {
            return match ($outcome) {
                MatchOutcomeEnum::DRAW => new MatchSimulationResult(1, 1),
                MatchOutcomeEnum::HOME => new MatchSimulationResult(2, 1),
                MatchOutcomeEnum::AWAY => new MatchSimulationResult(1, 2),
            };
        }
    };

    $randomGenerator = new FakeRandomGenerator(0.3); // Should select HOME (0.0-0.5)

    $simulator = new DefaultMatchSimulator(
        $outcomeCalculator,
        $goalGenerator,
        $randomGenerator
    );

    $result = $simulator->simulate($context);

    expect($result)->toBeInstanceOf(MatchSimulationResult::class)
        ->and($result->homeGoals)->toBeGreaterThanOrEqual(0)
        ->and($result->awayGoals)->toBeGreaterThanOrEqual(0);
});

test('it selects outcome based on random value and probabilities', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $outcomeCalculator = new class implements OutcomeCalculator {
        public function calculateProbabilities(MatchContext $context): array
        {
            return [
                MatchOutcomeEnum::HOME->value => 0.4, // 0.0 - 0.4
                MatchOutcomeEnum::DRAW->value => 0.3, // 0.4 - 0.7
                MatchOutcomeEnum::AWAY->value => 0.3, // 0.7 - 1.0
            ];
        }
    };

    $goalGenerator = new class implements GoalGenerator {
        public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
        {
            return match ($outcome) {
                MatchOutcomeEnum::DRAW => new MatchSimulationResult(1, 1),
                MatchOutcomeEnum::HOME => new MatchSimulationResult(2, 1),
                MatchOutcomeEnum::AWAY => new MatchSimulationResult(1, 2),
            };
        }
    };

    // Random value 0.2 should select HOME (0.0-0.4)
    $randomGenerator = new FakeRandomGenerator(0.2);
    $simulator = new DefaultMatchSimulator(
        $outcomeCalculator,
        $goalGenerator,
        $randomGenerator
    );

    $result = $simulator->simulate($context);

    expect($result->homeGoals)->toBe(2)
        ->and($result->awayGoals)->toBe(1);
});

test('it selects draw when random value falls in draw range', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $outcomeCalculator = new class implements OutcomeCalculator {
        public function calculateProbabilities(MatchContext $context): array
        {
            return [
                MatchOutcomeEnum::HOME->value => 0.4, // 0.0 - 0.4
                MatchOutcomeEnum::DRAW->value => 0.3, // 0.4 - 0.7
                MatchOutcomeEnum::AWAY->value => 0.3, // 0.7 - 1.0
            ];
        }
    };

    $goalGenerator = new class implements GoalGenerator {
        public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
        {
            return match ($outcome) {
                MatchOutcomeEnum::DRAW => new MatchSimulationResult(1, 1),
                MatchOutcomeEnum::HOME => new MatchSimulationResult(2, 1),
                MatchOutcomeEnum::AWAY => new MatchSimulationResult(1, 2),
            };
        }
    };

    // Random value 0.5 should select DRAW (0.4-0.7)
    $randomGenerator = new FakeRandomGenerator(0.5);
    $simulator = new DefaultMatchSimulator(
        $outcomeCalculator,
        $goalGenerator,
        $randomGenerator
    );

    $result = $simulator->simulate($context);

    expect($result->homeGoals)->toBe(1)
        ->and($result->awayGoals)->toBe(1);
});

test('it selects away win when random value falls in away range', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $outcomeCalculator = new class implements OutcomeCalculator {
        public function calculateProbabilities(MatchContext $context): array
        {
            return [
                MatchOutcomeEnum::HOME->value => 0.4, // 0.0 - 0.4
                MatchOutcomeEnum::DRAW->value => 0.3, // 0.4 - 0.7
                MatchOutcomeEnum::AWAY->value => 0.3, // 0.7 - 1.0
            ];
        }
    };

    $goalGenerator = new class implements GoalGenerator {
        public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
        {
            return match ($outcome) {
                MatchOutcomeEnum::DRAW => new MatchSimulationResult(1, 1),
                MatchOutcomeEnum::HOME => new MatchSimulationResult(2, 1),
                MatchOutcomeEnum::AWAY => new MatchSimulationResult(1, 2),
            };
        }
    };

    // Random value 0.8 should select AWAY (0.7-1.0)
    $randomGenerator = new FakeRandomGenerator(0.8);
    $simulator = new DefaultMatchSimulator(
        $outcomeCalculator,
        $goalGenerator,
        $randomGenerator
    );

    $result = $simulator->simulate($context);

    expect($result->homeGoals)->toBe(1)
        ->and($result->awayGoals)->toBe(2);
});

test('it defaults to draw when probabilities do not sum to 1', function () {
    $context = new MatchContext(
        homeTeamId: 1,
        awayTeamId: 2,
        homeEffectivePower: 70.0,
        awayEffectivePower: 50.0,
        week: 1,
    );

    $outcomeCalculator = new class implements OutcomeCalculator {
        public function calculateProbabilities(MatchContext $context): array
        {
            return [
                MatchOutcomeEnum::HOME->value => 0.3, // Sum < 1.0
                MatchOutcomeEnum::DRAW->value => 0.2,
                MatchOutcomeEnum::AWAY->value => 0.2,
            ];
        }
    };

    $goalGenerator = new class implements GoalGenerator {
        public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
        {
            return match ($outcome) {
                MatchOutcomeEnum::DRAW => new MatchSimulationResult(1, 1),
                MatchOutcomeEnum::HOME => new MatchSimulationResult(2, 1),
                MatchOutcomeEnum::AWAY => new MatchSimulationResult(1, 2),
            };
        }
    };

    // Random value 1.0 should default to DRAW
    $randomGenerator = new FakeRandomGenerator(1.0);
    $simulator = new DefaultMatchSimulator(
        $outcomeCalculator,
        $goalGenerator,
        $randomGenerator
    );

    $result = $simulator->simulate($context);

    expect($result->homeGoals)->toBe(1)
        ->and($result->awayGoals)->toBe(1);
});
