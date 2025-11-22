<?php

namespace App\MatchSimulation;

readonly class MatchSimulationResult
{
    public function __construct(
        public int $homeGoals,
        public int $awayGoals,
    )
    {
    }

    public function isDraw(): bool
    {
        return $this->homeGoals === $this->awayGoals;
    }

    public function homeWins(): bool
    {
        return $this->homeGoals > $this->awayGoals;
    }

    public function awayWins(): bool
    {
        return $this->awayGoals > $this->homeGoals;
    }

    public function getWinnerId(int $homeTeamId, int $awayTeamId): ?int
    {
        if ($this->isDraw()) {
            return null;
        }

        return $this->homeWins() ? $homeTeamId : $awayTeamId;
    }
}

