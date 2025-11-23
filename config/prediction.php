<?php

use App\Prediction\Algorithms\MonteCarlo\MonteCarloPredictionAlgorithm;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Prediction Algorithm
    |--------------------------------------------------------------------------
    |
    | This option controls the default prediction algorithm used for
    | calculating championship probabilities.
    |
    */

    'default_algorithm' => MonteCarloPredictionAlgorithm::class,

    /*
    |--------------------------------------------------------------------------
    | Monte Carlo Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Monte Carlo prediction algorithm.
    |
    */

    'monte_carlo' => [
        'simulation_count' => env('PREDICTION_SIMULATION_COUNT', 10000),

        'early_termination' => [
            'enabled' => true,
            'point_difference_threshold' => 9,
        ],
    ],
];
