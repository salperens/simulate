<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\League\TeamStandingData;
use App\Prediction\PredictionContext;
use Illuminate\Support\Collection;

readonly class EarlyTerminationChecker
{
    public function canTerminate(PredictionContext $context): bool
    {
        if ($context->standings->isEmpty()) {
            return false;
        }

        $sortedStandings = $this->sortStandings($context->standings);
        $leader = $sortedStandings->first();
        $second = $sortedStandings->get(1);

        if ($second === null) {
            return true;
        }

        $pointDifference = $leader->points - $second->points;
        $remainingMatches = $context->remainingFixtures->count();
        $maxPossiblePointsGain = $remainingMatches * 3;

        return $pointDifference > $maxPossiblePointsGain;
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
