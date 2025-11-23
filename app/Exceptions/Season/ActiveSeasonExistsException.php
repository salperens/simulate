<?php

namespace App\Exceptions\Season;

use App\Models\Season;
use RuntimeException;

class ActiveSeasonExistsException extends RuntimeException
{
    public static function create(Season $season): self
    {
        return new self(
            sprintf(
                'An active season already exists (%s). Please complete the current season before starting a new one.',
                $season->name ?? $season->year
            )
        );
    }
}
