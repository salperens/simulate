<?php

namespace App\Actions\Season;

use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Season;

readonly class GetSeasonModelByIdAction
{
    public function execute(int $seasonId): Season
    {
        $season = Season::find($seasonId);

        if ($season === null) {
            throw SeasonNotFoundException::id($seasonId);
        }

        return $season;
    }
}
