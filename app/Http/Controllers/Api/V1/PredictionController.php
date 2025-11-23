<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Prediction\CalculatePredictionsAction;
use App\Actions\Season\GetCurrentSeasonAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PredictionResource;
use Illuminate\Http\Request;

final class PredictionController extends Controller
{
    public function __construct(
        private readonly CalculatePredictionsAction $calculatePredictionsAction,
        private readonly GetCurrentSeasonAction     $getCurrentSeasonAction,
    )
    {
    }

    /**
     * Get predictions for a specific week.
     * If season_id is provided, use that season. Otherwise, use current season.
     */
    public function byWeek(Request $request, int $week): PredictionResource
    {
        $seasonId = $request->query('season_id');
        $result = $this->calculatePredictionsAction->execute($week, null, $seasonId !== null ? (int) $seasonId : null);

        return new PredictionResource($result);
    }

    /**
     * Get predictions for the current week.
     */
    public function current(): PredictionResource
    {
        $seasonData = $this->getCurrentSeasonAction->execute();
        $result = $this->calculatePredictionsAction->execute($seasonData->currentWeek);

        return new PredictionResource($result);
    }
}
