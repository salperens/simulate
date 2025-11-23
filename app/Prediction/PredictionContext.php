<?php

namespace App\Prediction;

use App\Data\League\TeamStandingData;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Support\Collection;

readonly class PredictionContext
{
    /**
     * @param Season $season
     * @param int $currentWeek
     * @param Collection<int, TeamStandingData> $standings
     * @param Collection<int, Fixture> $remainingFixtures
     * @param PredictionTypeEnum $type
     */
    public function __construct(
        public Season             $season,
        public int                $currentWeek,
        public Collection         $standings,
        public Collection         $remainingFixtures,
        public PredictionTypeEnum $type = PredictionTypeEnum::CHAMPIONSHIP,
    )
    {
    }
}
