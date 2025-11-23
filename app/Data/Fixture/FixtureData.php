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
        public int                $week_number,
        public TeamData           $home_team,
        public TeamData           $away_team,
        public ?int               $home_score,
        public ?int               $away_score,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?DateTimeInterface $played_at,
    )
    {
    }
}

