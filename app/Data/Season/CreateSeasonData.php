<?php

namespace App\Data\Season;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CreateSeasonData extends Data
{
    public function __construct(
        public readonly int     $year,
        public readonly array   $teamIds,
        public readonly ?string $name = null,
    )
    {
    }
}
