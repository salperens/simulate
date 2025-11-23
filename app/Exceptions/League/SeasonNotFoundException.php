<?php

namespace App\Exceptions\League;

use RuntimeException;

class SeasonNotFoundException extends RuntimeException
{
    public static function year(int $year): self
    {
        return new self("No active season found for the $year year.");
    }

    public static function id(int $id): self
    {
        return new self("Season with ID $id not found.");
    }
}

