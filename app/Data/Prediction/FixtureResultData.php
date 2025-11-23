<?php

namespace App\Data\Prediction;

use Spatie\LaravelData\Data;

class FixtureResultData extends Data
{
    public function __construct(
        public readonly int $homeTeamId,
        public readonly int $awayTeamId,
        public readonly int $homeScore,
        public readonly int $awayScore,
    )
    {
    }
}
