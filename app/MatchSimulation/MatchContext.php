<?php

namespace App\MatchSimulation;

readonly class MatchContext
{
    public function __construct(
        public int   $homeTeamId,
        public int   $awayTeamId,
        public float $homeEffectivePower,
        public float $awayEffectivePower,
        public int   $week,
        public ?int  $seasonId = null,
        public bool  $isDecisiveMatch = false,
    )
    {
    }
}

