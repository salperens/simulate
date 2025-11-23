<?php

namespace App\Data\Fixture;

use Spatie\LaravelData\Data;

class TeamData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
    )
    {
    }
}

