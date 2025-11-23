<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\League\TeamStandingData;
use Illuminate\Support\Collection;

readonly class ChampionFinder
{
    /**
     * @param Collection<int, TeamStandingData> $standings
     * @return TeamStandingData
     */
    public function find(Collection $standings): TeamStandingData
    {
        return $this->sortStandings($standings)->first();
    }

    /**
     * @param Collection<int, TeamStandingData> $standings
     * @return Collection<int, TeamStandingData>
     */
    private function sortStandings(Collection $standings): Collection
    {
        return $standings->sort(function (TeamStandingData $a, TeamStandingData $b) {
            if ($a->points !== $b->points) {
                return $b->points <=> $a->points;
            }

            if ($a->goalDifference !== $b->goalDifference) {
                return $b->goalDifference <=> $a->goalDifference;
            }

            return $b->goalsFor <=> $a->goalsFor;
        })->values();
    }
}
