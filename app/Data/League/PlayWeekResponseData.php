<?php

namespace App\Data\League;

use Spatie\LaravelData\Data;

class PlayWeekResponseData extends Data
{
    public function __construct(
        public readonly int $week,
        public readonly int $matches_played,
    ) {
    }
}
