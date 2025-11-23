<?php

namespace App\Exceptions\Season;

use RuntimeException;

class CannotStartSeasonException extends RuntimeException
{
    public static function notDraft(): self
    {
        return new self('Only draft seasons can be started.');
    }
}
