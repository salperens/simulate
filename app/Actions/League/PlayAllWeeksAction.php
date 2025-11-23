<?php

namespace App\Actions\League;

use App\Actions\Match\SimulateAllWeeksAction;
use App\Data\League\PlayAllResponseData;
use App\Models\Season;

readonly class PlayAllWeeksAction
{
    public function __construct(private SimulateAllWeeksAction $simulateAllWeeksAction)
    {
    }

    public function execute(Season $season): PlayAllResponseData
    {
        $matchesPlayed = $this->simulateAllWeeksAction->execute($season->id);

        return new PlayAllResponseData(
            matches_played: $matchesPlayed,
        );
    }
}
