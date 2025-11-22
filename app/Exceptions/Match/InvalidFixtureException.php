<?php

namespace App\Exceptions\Match;

use RuntimeException;

class InvalidFixtureException extends RuntimeException
{
    public static function missingTeams(): self
    {
        return new self('Fixture must have both home and away teams.');
    }
}

