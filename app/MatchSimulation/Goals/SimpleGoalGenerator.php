<?php

namespace App\MatchSimulation\Goals;

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\Contracts\GoalGenerator;
use App\MatchSimulation\Contracts\RandomGenerator;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;

readonly class SimpleGoalGenerator implements GoalGenerator
{
    public function __construct(private RandomGenerator $randomGenerator)
    {
    }

    public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult
    {
        return match ($outcome) {
            MatchOutcomeEnum::DRAW => $this->generateDrawScore(),
            MatchOutcomeEnum::HOME => $this->generateHomeWinScore(),
            MatchOutcomeEnum::AWAY => $this->generateAwayWinScore(),
        };
    }

    private function generateDrawScore(): MatchSimulationResult
    {
        $goals = $this->randomInt(0, 3);
        return new MatchSimulationResult($goals, $goals);
    }

    private function generateHomeWinScore(): MatchSimulationResult
    {
        $homeGoals = $this->randomInt(1, 4);
        $awayGoals = $this->randomInt(0, max(0, $homeGoals - 1));
        return new MatchSimulationResult($homeGoals, $awayGoals);
    }

    private function generateAwayWinScore(): MatchSimulationResult
    {
        $awayGoals = $this->randomInt(1, 4);
        $homeGoals = $this->randomInt(0, max(0, $awayGoals - 1));
        return new MatchSimulationResult($homeGoals, $awayGoals);
    }

    private function randomInt(int $min, int $max): int
    {
        if ($min > $max) {
            return $min;
        }

        $random = $this->randomGenerator->float01();
        return (int)floor($min + $random * ($max - $min + 1));
    }
}

