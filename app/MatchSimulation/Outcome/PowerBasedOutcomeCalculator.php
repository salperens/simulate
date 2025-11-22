<?php

namespace App\MatchSimulation\Outcome;

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\Contracts\OutcomeCalculator;
use App\MatchSimulation\MatchContext;

class PowerBasedOutcomeCalculator implements OutcomeCalculator
{
    private const DRAW_BASE_PROBABILITY = 0.20;

    public function calculateProbabilities(MatchContext $context): array
    {
        $totalPower = $context->homeEffectivePower + $context->awayEffectivePower;

        if ($totalPower <= 0) {
            return [
                MatchOutcomeEnum::HOME->value => 0.33,
                MatchOutcomeEnum::DRAW->value => 0.34,
                MatchOutcomeEnum::AWAY->value => 0.33,
            ];
        }

        $drawProbability = self::DRAW_BASE_PROBABILITY;
        $nonDrawProbability = 1.0 - $drawProbability;

        $homePowerRatio = $context->homeEffectivePower / $totalPower;
        $awayPowerRatio = $context->awayEffectivePower / $totalPower;

        $homeWinProbability = $nonDrawProbability * $homePowerRatio;
        $awayWinProbability = $nonDrawProbability * $awayPowerRatio;

        return [
            MatchOutcomeEnum::HOME->value => $homeWinProbability,
            MatchOutcomeEnum::DRAW->value => $drawProbability,
            MatchOutcomeEnum::AWAY->value => $awayWinProbability,
        ];
    }
}

