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
        public readonly int    $goalsFor,
        public readonly int    $goalsAgainst,
        public readonly int    $goalDifference,
        public readonly int    $points,
    )
    {
    }
}
