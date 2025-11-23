<?php

namespace App\Actions\League;

use App\Actions\Match\SimulateWeekAction;
use App\Actions\Prediction\CalculatePredictionsIfApplicableAction;
use App\Data\League\PlayWeekResponseData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\CannotPlayMatchesException;
use App\Models\Fixture;
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

        $this->validateWeekOrder($season, $weekNumber);

        $matchesPlayed = $this->simulateWeekAction->execute($weekNumber, $season->id);

        $this->calculatePredictionsIfApplicableAction->execute($season, $weekNumber);

        return new PlayWeekResponseData(
            week: $weekNumber,
            matchesPlayed: $matchesPlayed,
        );
    }

    private function validateWeekOrder(Season $season, int $requestedWeek): void
    {
        $lastPlayedWeek = $season->fixtures()
            ->whereNotNull('played_at')
            ->max('week_number');

        $nextPlayableWeek = $lastPlayedWeek === null ? 1 : $lastPlayedWeek + 1;

        if ($requestedWeek !== $nextPlayableWeek) {
            throw CannotPlayMatchesException::weekOutOfOrder($requestedWeek, $nextPlayableWeek);
        }

        $hasUnplayedWeeksBefore = Fixture::query()
            ->where('season_id', $season->id)
            ->where('week_number', '<', $requestedWeek)
            ->whereNull('played_at')
            ->exists();

        if ($hasUnplayedWeeksBefore) {
            throw CannotPlayMatchesException::weekOutOfOrder($requestedWeek, $nextPlayableWeek);
        }
    }
}
