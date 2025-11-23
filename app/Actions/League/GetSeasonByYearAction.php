<?php

namespace App\Actions\League;

use App\Models\Season;
use App\Exceptions\League\SeasonNotFoundException;

readonly class GetSeasonByYearAction
{
    public function execute(int $year): Season
    {
        $season = Season::query()->where('year', $year)->first();

        if (!$season) {
            throw SeasonNotFoundException::year($year);
        }

        return $season;
    }
}

