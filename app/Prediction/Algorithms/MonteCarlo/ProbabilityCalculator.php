<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\League\TeamStandingData;
use App\Data\Prediction\TeamPredictionData;
use Illuminate\Support\Collection;

readonly class ProbabilityCalculator
{
    public function __construct(public int $simulationCount)
    {
    }

    /**
     * @param Collection<int, TeamStandingData> $standings
     * @param array<int, int> $championCounts
     * @return Collection<int, TeamPredictionData>
     */
    public function calculate(Collection $standings, array $championCounts): Collection
    {
        return $standings->map(function (TeamStandingData $standing) use ($championCounts) {
            $count = $championCounts[$standing->id] ?? 0;
            $probability = ($count / $this->simulationCount) * 100;

                return new TeamPredictionData(
                    teamId: $standing->id,
                    teamName: $standing->name,
                    winProbability: round($probability, 2),
                );
        });
    }
}
