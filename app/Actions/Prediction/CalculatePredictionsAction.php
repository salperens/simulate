<?php

namespace App\Actions\Prediction;

use App\Actions\League\CalculateStandingsAction;
use App\Actions\Season\GetSeasonByIdOrCurrentAction;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Exceptions\Prediction\PredictionNotAvailableException;
use App\Models\ChampionshipPrediction;
use App\Models\Season;
use App\Prediction\Contracts\PredictionAlgorithm;
use App\Prediction\PredictionContext;
use App\Prediction\PredictionResult;

readonly class CalculatePredictionsAction
{
    public function __construct(
        private GetSeasonByIdOrCurrentAction $getSeasonByIdOrCurrentAction,
        private CalculateStandingsAction     $calculateStandingsAction,
        private PredictionAlgorithm          $predictionAlgorithm,
    )
    {
    }

    public function execute(
        int                $week,
        ?int               $year = null,
        ?int               $seasonId = null,
        PredictionTypeEnum $type = PredictionTypeEnum::CHAMPIONSHIP,
    ): PredictionResult
    {
        $season = $this->getSeasonByIdOrCurrentAction->execute($seasonId, $year);

        $totalWeeks = $season->getTotalWeeks();
        $this->validatePredictionWindow($week, $totalWeeks);

        $standings = $this->calculateStandingsAction->execute($season);

        $remainingFixtures = $season->fixtures()
            ->whereNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $context = new PredictionContext(
            season: $season,
            currentWeek: $week,
            standings: $standings,
            remainingFixtures: $remainingFixtures,
            type: $type,
        );

        $result = $this->predictionAlgorithm->calculate($context);

        $this->savePredictions($season, $result);

        return $result;
    }

    private function savePredictions(Season $season, PredictionResult $result): void
    {
        $existing = ChampionshipPrediction::query()
            ->where('season_id', $season->id)
            ->where('week_number', $result->week)
            ->get()
            ->keyBy('team_id');

        foreach ($result->predictions as $prediction) {
            if ($existing->has($prediction->teamId)) {
                $existing->get($prediction->teamId)->update([
                    'win_probability' => $prediction->winProbability,
                ]);
            } else {
                ChampionshipPrediction::create([
                    'season_id'       => $season->id,
                    'week_number'     => $result->week,
                    'team_id'         => $prediction->teamId,
                    'win_probability' => $prediction->winProbability,
                ]);
            }
        }
    }

    /**
     * Validate that predictions can be shown for the given week.
     * Predictions are only available within the last 3 weeks of the season.
     */
    private function validatePredictionWindow(int $week, int $totalWeeks): void
    {
        // Predictions should be shown within the last 3 weeks
        // Note: Original requirement was "after the 4th week", but we use "last 3 weeks"
        // to accommodate variable team numbers (not fixed to 4 teams)
        $lastThreeWeeksStart = max(1, $totalWeeks - 2);
        if ($week < $lastThreeWeeksStart) {
            throw PredictionNotAvailableException::notInPredictionWindow($week, $totalWeeks);
        }
    }
}
