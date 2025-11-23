<?php

namespace App\Prediction\Contracts;

use App\Prediction\PredictionContext;
use App\Prediction\PredictionResult;

interface PredictionAlgorithm
{
    /**
     * Calculate championship prediction probabilities.
     *
     * @param PredictionContext $context
     * @return PredictionResult
     */
    public function calculate(PredictionContext $context): PredictionResult;
}
