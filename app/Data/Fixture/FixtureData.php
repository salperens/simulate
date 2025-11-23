<?php

namespace App\Data\Fixture;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class FixtureData extends Data
{
    public function __construct(
        public int                $id,
        public int                $weekNumber,
        public TeamData           $homeTeam,
        public TeamData           $awayTeam,
        public ?int               $homeScore,
        public ?int               $awayScore,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?DateTimeInterface $playedAt,
    )
    {
    }
}
