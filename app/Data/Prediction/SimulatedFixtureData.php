<?php

namespace App\Data\Prediction;

use Spatie\LaravelData\Data;

class SimulatedFixtureData extends Data
{
    public function __construct(
        public readonly int $fixtureId,
        public readonly int $homeScore,
        public readonly int $awayScore,
    )
    {
    }
}
