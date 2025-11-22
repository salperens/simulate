<?php

namespace App\Exceptions\Fixture;

use RuntimeException;

class CannotGenerateFixturesException extends RuntimeException
{
    public static function noTeams(): self
    {
        return new self('Cannot generate fixtures: Season has no teams.');
    }
}
