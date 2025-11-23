<?php

namespace App\Actions\Prediction;

use App\Models\Season;

readonly class CalculatePredictionsIfApplicableAction
{
    public function __construct(private CalculatePredictionsAction $calculatePredictionsAction)
    {
    }

    /**
     * Calculate predictions for a week if it's in the prediction window.
     * Silently skips if week is not in prediction window.
     *
     * @param Season $season
     * @param int $weekNumber
     * @return void
     */
    public function execute(Season $season, int $weekNumber): void
    {
        $totalWeeks = $season->getTotalWeeks();
        $lastThreeWeeksStart = max(1, $totalWeeks - 2);

        if ($weekNumber >= $lastThreeWeeksStart) {
            $this->calculatePredictionsAction->execute(
                week: $weekNumber,
                seasonId: $season->id,
            );
        }
    }
}
