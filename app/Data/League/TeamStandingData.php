<?php

namespace App\Data\League;

use Spatie\LaravelData\Data;

class TeamStandingData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly int    $played,
        public readonly int    $won,
        public readonly int    $drawn,
        public readonly int    $lost,
        public readonly int    $goals_for,
        public readonly int    $goals_against,
        public readonly int    $goal_difference,
        public readonly int    $points,
    )
    {
    }
}

