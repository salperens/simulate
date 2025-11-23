<?php

namespace App\Actions\League;

use App\Actions\Match\SimulateWeekAction;
use App\Data\League\PlayWeekResponseData;
use App\Models\Season;

readonly class PlayWeekAction
{
    public function __construct(
        private SimulateWeekAction $simulateWeekAction
    ) {
    }

    public function execute(Season $season, int $weekNumber): PlayWeekResponseData
    {
        $matchesPlayed = $this->simulateWeekAction->execute($weekNumber, $season->id);

        return new PlayWeekResponseData(
            week: $weekNumber,
            matches_played: $matchesPlayed,
        );
    }
}
