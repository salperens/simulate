<?php

namespace App\Enums\Prediction;

enum PredictionTypeEnum: string
{
    case CHAMPIONSHIP = 'championship';
    case TOP_2 = 'top_2';
    case PLAYOFF = 'playoff';
    case RELEGATION = 'relegation';

    public function label(): string
    {
        return match ($this) {
            self::CHAMPIONSHIP => 'Championship',
            self::TOP_2 => 'Top 2',
            self::PLAYOFF => 'Playoff',
            self::RELEGATION => 'Relegation',
        };
    }
}
