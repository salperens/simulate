<?php

namespace App\Exceptions\Match;

use RuntimeException;

class FixtureAlreadyPlayedException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Fixture has already been played.');
    }
}

