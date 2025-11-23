<?php

namespace App\Data\League;

use Spatie\LaravelData\Data;

class PlayAllResponseData extends Data
{
    public function __construct(
        public readonly int $matches_played,
    ) {
    }
}
