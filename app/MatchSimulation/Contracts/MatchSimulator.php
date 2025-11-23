<?php

namespace App\MatchSimulation\Contracts;

use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;

interface MatchSimulator
{
    public function simulate(MatchContext $context): MatchSimulationResult;
}
