<?php

namespace App\MatchSimulation\Contracts;

use App\Enums\Match\MatchOutcomeEnum;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;

interface GoalGenerator
{
    /**
     * Generate score based on the outcome.
     *
     * @param MatchContext $context
     * @param MatchOutcomeEnum $outcome
     * @return MatchSimulationResult
     */
    public function generateScore(MatchContext $context, MatchOutcomeEnum $outcome): MatchSimulationResult;
}
