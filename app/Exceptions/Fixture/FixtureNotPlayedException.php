<?php

namespace App\Exceptions\Fixture;

use RuntimeException;

class FixtureNotPlayedException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Cannot update fixture score. The match has not been played yet.');
    }
}
