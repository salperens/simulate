<?php

namespace App\Exceptions\Prediction;

use RuntimeException;

class PredictionNotAvailableException extends RuntimeException
{
    public static function notInPredictionWindow(int $week, int $totalWeeks): self
    {
        $lastThreeWeeksStart = max(1, $totalWeeks - 2);
        return new self(
            sprintf(
                'Predictions are only available within the last 3 weeks (weeks %d-%d). Current week: %d, Total weeks: %d',
                $lastThreeWeeksStart,
                $totalWeeks,
                $week,
                $totalWeeks,
            )
        );
    }
}
