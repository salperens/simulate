<?php

namespace App\Prediction;

use App\Data\Prediction\TeamPredictionData;
use App\Enums\Prediction\PredictionTypeEnum;
use Illuminate\Support\Collection;

readonly class PredictionResult
{
    /**
     * @param int $week
     * @param PredictionTypeEnum $type
     * @param Collection<int, TeamPredictionData> $predictions
     * @param int $simulationsRun
     * @param bool $earlyTerminated
     */
    public function __construct(
        public int                $week,
        public PredictionTypeEnum $type,
        public Collection         $predictions,
        public int                $simulationsRun,
        public bool               $earlyTerminated = false,
    )
    {
    }
}
