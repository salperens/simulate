<?php

namespace App\Actions\League;

use App\Actions\Match\SimulateWeekAction;
use App\Actions\Prediction\CalculatePredictionsIfApplicableAction;
use App\Data\League\PlayWeekResponseData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\CannotPlayMatchesException;
use App\Models\Season;

readonly class PlayWeekAction
{
    public function __construct(
        private SimulateWeekAction                     $simulateWeekAction,
        private CalculatePredictionsIfApplicableAction $calculatePredictionsIfApplicableAction,
    )
    {
    }

    public function execute(Season $season, int $weekNumber): PlayWeekResponseData
    {
        if ($season->status === SeasonStatusEnum::COMPLETED) {
            throw CannotPlayMatchesException::seasonCompleted();
        }

        $matchesPlayed = $this->simulateWeekAction->execute($weekNumber, $season->id);

        $this->calculatePredictionsIfApplicableAction->execute($season, $weekNumber);

        return new PlayWeekResponseData(
            week: $weekNumber,
            matchesPlayed: $matchesPlayed,
        );
    }
}
