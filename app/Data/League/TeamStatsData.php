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
        public int $goalsFor = 0,
        public int $goalsAgainst = 0,
    )
    {
    }

    public function getGoalDifference(): int
    {
        return $this->goalsFor - $this->goalsAgainst;
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
            goalsFor: $this->goalsFor,
            goalsAgainst: $this->goalsAgainst,
        );
    }

    public function addGoals(int $for, int $against): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost,
            goalsFor: $this->goalsFor + $for,
            goalsAgainst: $this->goalsAgainst + $against,
        );
    }

    public function incrementWon(): self
    {
        return new self(
            played: $this->played,
            won: $this->won + 1,
            drawn: $this->drawn,
            lost: $this->lost,
            goalsFor: $this->goalsFor,
            goalsAgainst: $this->goalsAgainst,
        );
    }

    public function incrementDrawn(): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn + 1,
            lost: $this->lost,
            goalsFor: $this->goalsFor,
            goalsAgainst: $this->goalsAgainst,
        );
    }

    public function incrementLost(): self
    {
        return new self(
            played: $this->played,
            won: $this->won,
            drawn: $this->drawn,
            lost: $this->lost + 1,
            goalsFor: $this->goalsFor,
            goalsAgainst: $this->goalsAgainst,
        );
    }
}
