<?php

namespace App\Exceptions\Season;

use RuntimeException;

class SeasonYearAlreadyExistsException extends RuntimeException
{
    public static function create(int $year): self
    {
        return new self(
            sprintf(
                'A season for the year %d already exists. Please select a different year.',
                $year
            )
        );
    }
}
