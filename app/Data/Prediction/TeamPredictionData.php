<?php

namespace App\Data\Prediction;

use Spatie\LaravelData\Data;

class TeamPredictionData extends Data
{
    public function __construct(
        public readonly int    $teamId,
        public readonly string $teamName,
        public readonly float  $winProbability, // 0.0 - 100.0
    )
    {
    }
}
