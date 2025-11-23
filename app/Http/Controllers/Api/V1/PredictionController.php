<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Prediction\CalculatePredictionsAction;
use App\Actions\Season\GetCurrentSeasonAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PredictionResource;

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
     */
    public function byWeek(int $week): PredictionResource
    {
        $result = $this->calculatePredictionsAction->execute($week);

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
