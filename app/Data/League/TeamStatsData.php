<?php

namespace App\Data\League;

use Spatie\LaravelData\Data;

class TeamStatsData extends Data
{
    public function __construct(
        public int $played = 0,
        public int $won = 0,
        public int $drawn = 0,
        public int $lost = 0,
        public int $goals_for = 0,
        public int $goals_against = 0,
    )
    {
    }

    public function getGoalDifference(): int
    {
        return $this->goals_for - $this->goals_against;
    }

    public function getPoints(): int
    {
        return ($this->won * 3) + ($this->drawn * 1);
    }

    public function incrementPlayed(): self
    {
        return new self(
            played: $this->played + 1,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost,
            goals_for: $this->goals_for,
            goals_against: $this->goals_against,
        );
    }

    public function addGoals(int $for, int $against): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost,
            goals_for: $this->goals_for + $for,
            goals_against: $this->goals_against + $against,
        );
    }

    public function incrementWon(): self
    {
        return new self(
            played: $this->played,
            won: $this->won + 1,
            drawn: $this->drawn,
            lost: $this->lost,
            goals_for: $this->goals_for,
            goals_against: $this->goals_against,
        );
    }

    public function incrementDrawn(): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn + 1,
            lost: $this->lost,
            goals_for: $this->goals_for,
            goals_against: $this->goals_against,
        );
    }

    public function incrementLost(): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost + 1,
            goals_for: $this->goals_for,
            goals_against: $this->goals_against,
        );
    }
}

