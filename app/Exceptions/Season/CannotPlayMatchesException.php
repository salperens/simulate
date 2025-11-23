<?php

namespace App\Exceptions\Season;

use RuntimeException;

class CannotPlayMatchesException extends RuntimeException
{
    public static function seasonCompleted(): self
    {
        return new self('Cannot play matches. The season has been completed.');
    }

    public static function weekOutOfOrder(int $requestedWeek, int $nextPlayableWeek): self
    {
        return new self("Cannot play week {$requestedWeek}. You must play weeks in order. The next playable week is {$nextPlayableWeek}.");
    }
}
