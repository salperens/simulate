<?php

namespace App\Exceptions\Season;

use RuntimeException;

class CannotPlayMatchesException extends RuntimeException
{
    public static function seasonCompleted(): self
    {
        return new self('Cannot play matches. The season has been completed.');
    }
}
