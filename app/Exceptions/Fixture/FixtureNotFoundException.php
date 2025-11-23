<?php

namespace App\Exceptions\Fixture;

use RuntimeException;

class FixtureNotFoundException extends RuntimeException
{
    public static function create(int $fixtureId): self
    {
        return new self("Fixture with ID {$fixtureId} not found.");
    }
}
