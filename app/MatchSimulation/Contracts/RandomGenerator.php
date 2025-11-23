<?php

namespace App\MatchSimulation\Contracts;

interface RandomGenerator
{
    /**
     * Generate a random float between 0.0 and 1.0.
     *
     * @return float
     */
    public function float01(): float;
}
