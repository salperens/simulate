<?php

namespace App\Data\Season;

use App\Enums\Season\SeasonStatusEnum;
use DateTimeInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class SeasonData extends Data
{
    public function __construct(
        public int                $id,
        public int                $year,
        public ?string            $name,
        public SeasonStatusEnum   $status,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?DateTimeInterface $startDate,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?DateTimeInterface $endDate,
        public int                $currentWeek,
        public int                $totalWeeks,
    )
    {
    }
}
