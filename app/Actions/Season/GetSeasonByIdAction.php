<?php

namespace App\Actions\Season;

use App\Data\Season\SeasonData;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Season;

readonly class GetSeasonByIdAction
{
    public function execute(int $seasonId): SeasonData
    {
        $season = Season::find($seasonId);

        if ($season === null) {
            throw SeasonNotFoundException::id($seasonId);
        }

        return $this->createSeasonData($season);
    }

    private function createSeasonData(Season $season): SeasonData
    {
        $currentWeek = $this->calculateCurrentWeek($season);
        $totalWeeks = $season->getTotalWeeks();

        return new SeasonData(
            id: $season->id,
            year: $season->year,
            name: $season->name,
            status: $season->status,
            startDate: $season->start_date,
            endDate: $season->end_date,
            currentWeek: $currentWeek,
            totalWeeks: $totalWeeks,
        );
    }

    private function calculateCurrentWeek(Season $season): int
    {
        $lastPlayedWeek = $season->fixtures()
            ->whereNotNull('played_at')
            ->max('week_number');

        if ($lastPlayedWeek === null) {
            return 1;
        }

        return $lastPlayedWeek;
    }
}
