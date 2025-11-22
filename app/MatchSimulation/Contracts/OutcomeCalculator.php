<?php

namespace App\MatchSimulation\Contracts;

use App\MatchSimulation\MatchContext;

interface OutcomeCalculator
{
    /**
     * Calculate probabilities for home win, draw, and away win.
     *
     * @param MatchContext $context
     * @return array<string, float> Array keyed by MatchOutcomeEnum values
     */
    public function calculateProbabilities(MatchContext $context): array;
}

