<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\League\TeamStandingData;
use App\Data\Prediction\TeamPredictionData;
use App\Prediction\Contracts\PredictionAlgorithm;
use App\Prediction\PredictionContext;
use App\Prediction\PredictionResult;
use Illuminate\Support\Collection;

readonly class MonteCarloPredictionAlgorithm implements PredictionAlgorithm
{
    public function __construct(
        private FixtureSimulator        $fixtureSimulator,
        private StandingsCalculator     $standingsCalculator,
        private ChampionFinder          $championFinder,
        private ProbabilityCalculator   $probabilityCalculator,
        private EarlyTerminationChecker $earlyTerminationChecker,
    )
    {
    }

    public function calculate(PredictionContext $context): PredictionResult
    {
        if ($context->remainingFixtures->isEmpty()) {
            return $this->createFinalResult($context);
        }

        if ($this->earlyTerminationChecker->canTerminate($context)) {
            return $this->createFinalResult($context);
        }

        return $this->runMonteCarloSimulation($context);
    }

    private function createFinalResult(PredictionContext $context): PredictionResult
    {
        $champion = $this->championFinder->find($context->standings);

        $predictions = $context->standings->map(function ($standing) use ($champion) {
            return new TeamPredictionData(
                teamId: $standing->id,
                teamName: $standing->name,
                winProbability: $standing->id === $champion->id ? 100.0 : 0.0,
            );
        });

        return new PredictionResult(
            week: $context->currentWeek,
            type: $context->type,
            predictions: $predictions,
            simulationsRun: 0,
            earlyTerminated: true,
        );
    }

    private function runMonteCarloSimulation(PredictionContext $context): PredictionResult
    {
        $championCounts = $this->initializeChampionCounts($context->standings);
        $simulationCount = $this->probabilityCalculator->simulationCount;

        for ($i = 0; $i < $simulationCount; $i++) {
            $simulatedFixtures = $this->fixtureSimulator->simulateRemaining($context);
            $simulatedStandings = $this->standingsCalculator->calculate($context, $simulatedFixtures);
            $champion = $this->championFinder->find($simulatedStandings);
            $championCounts[$champion->id]++;
        }

        $predictions = $this->probabilityCalculator->calculate($context->standings, $championCounts);

        return new PredictionResult(
            week: $context->currentWeek,
            type: $context->type,
            predictions: $predictions,
            simulationsRun: $simulationCount,
            earlyTerminated: false,
        );
    }

    /**
     * @param Collection<int, TeamStandingData> $standings
     * @return array<int, int>
     */
    private function initializeChampionCounts(Collection $standings): array
    {
        $counts = [];
        foreach ($standings as $standing) {
            $counts[$standing->id] = 0;
        }
        return $counts;
    }
}
