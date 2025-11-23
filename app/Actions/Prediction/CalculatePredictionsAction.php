<?php

namespace App\Actions\Prediction;

use App\Actions\League\CalculateStandingsAction;
use App\Actions\League\GetSeasonByYearAction;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Models\ChampionshipPrediction;
use App\Models\Season;
use App\Prediction\Contracts\PredictionAlgorithm;
use App\Prediction\PredictionContext;
use App\Prediction\PredictionResult;

readonly class CalculatePredictionsAction
{
    public function __construct(
        private GetSeasonByYearAction    $getSeasonByYearAction,
        private CalculateStandingsAction $calculateStandingsAction,
        private PredictionAlgorithm      $predictionAlgorithm,
    )
    {
    }

    public function execute(
        int                $week,
        ?int               $year = null,
        PredictionTypeEnum $type = PredictionTypeEnum::CHAMPIONSHIP,
    ): PredictionResult
    {
        $year = $year ?? now()->year;
        $season = $this->getSeasonByYearAction->execute($year);

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
}
