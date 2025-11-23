<?php

namespace App\Actions\League;

use App\Actions\Match\SimulateWeekAction;
use App\Actions\Prediction\CalculatePredictionsIfApplicableAction;
use App\Data\League\PlayAllResponseData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\CannotPlayMatchesException;
use App\Models\Fixture;
use App\Models\Season;

readonly class PlayUnplayedWeeksAction
{
    public function __construct(
        private SimulateWeekAction                     $simulateWeekAction,
        private CalculatePredictionsIfApplicableAction $calculatePredictionsIfApplicableAction,
    )
    {
    }

    public function execute(Season $season): PlayAllResponseData
    {
        if ($season->status === SeasonStatusEnum::COMPLETED) {
            throw CannotPlayMatchesException::seasonCompleted();
        }

        $totalMatchesPlayed = 0;

        $weeksWithUnplayedFixtures = Fixture::query()
            ->where('season_id', $season->id)
            ->whereNull('played_at')
            ->distinct()
            ->orderBy('week_number')
            ->pluck('week_number')
            ->values();

        foreach ($weeksWithUnplayedFixtures as $weekNumber) {
            $matchesPlayed = $this->simulateWeekAction->execute($weekNumber, $season->id);
            $totalMatchesPlayed += $matchesPlayed;

            $this->calculatePredictionsIfApplicableAction->execute($season, $weekNumber);
        }

        return new PlayAllResponseData(
            matchesPlayed: $totalMatchesPlayed,
        );
    }
}
