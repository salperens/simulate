<?php

namespace App\Exceptions\MatchSimulation;

use InvalidArgumentException;

class InvalidRandomValueException extends InvalidArgumentException
{
    public static function outOfRange(float $value): self
    {
        return new self(
            sprintf('Random value must be between 0.0 and 1.0, %f given.', $value)
        );
    }
}

