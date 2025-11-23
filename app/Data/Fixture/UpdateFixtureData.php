<?php

namespace App\Data\Fixture;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UpdateFixtureData extends Data
{
    public function __construct(
        public int $homeScore,
        public int $awayScore,
    )
    {
    }
}
