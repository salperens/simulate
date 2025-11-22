<?php

namespace App\MatchSimulation;

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\Contracts\GoalGenerator;
use App\MatchSimulation\Contracts\MatchSimulator;
use App\MatchSimulation\Contracts\OutcomeCalculator;
use App\MatchSimulation\Contracts\RandomGenerator;

readonly class DefaultMatchSimulator implements MatchSimulator
{
    public function __construct(
        private OutcomeCalculator $outcomeCalculator,
        private GoalGenerator     $goalGenerator,
        private RandomGenerator   $randomGenerator,
    )
    {
    }

    public function simulate(MatchContext $context): MatchSimulationResult
    {
        $probabilities = $this->outcomeCalculator->calculateProbabilities($context);
        $random = $this->randomGenerator->float01();

        $outcome = $this->selectOutcome($probabilities, $random);
        return $this->goalGenerator->generateScore($context, $outcome);
    }

    private function selectOutcome(array $probabilities, float $random): MatchOutcomeEnum
    {
        $cumulative = 0.0;

        foreach ($probabilities as $outcomeValue => $probability) {
            $cumulative += $probability;
            if ($random < $cumulative) {
                return MatchOutcomeEnum::from($outcomeValue);
            }
        }

        return MatchOutcomeEnum::DRAW;
    }
}

