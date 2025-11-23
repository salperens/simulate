<?php

namespace App\Exceptions\Season;

use RuntimeException;

class CannotCompleteSeasonException extends RuntimeException
{
    public static function notActive(): self
    {
        return new self('Only active seasons can be completed.');
    }

    public static function notAllMatchesPlayed(): self
    {
        return new self('Cannot complete season. Not all matches have been played.');
    }
}
