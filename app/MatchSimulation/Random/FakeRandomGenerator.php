<?php

namespace App\MatchSimulation\Random;

use App\Exceptions\MatchSimulation\InvalidRandomValueException;
use App\MatchSimulation\Contracts\RandomGenerator;

readonly class FakeRandomGenerator implements RandomGenerator
{
    public function __construct(private float $fixedValue)
    {
        if ($fixedValue < 0.0 || $fixedValue > 1.0) {
            throw InvalidRandomValueException::outOfRange($fixedValue);
        }
    }

    public function float01(): float
    {
        return $this->fixedValue;
    }
}
