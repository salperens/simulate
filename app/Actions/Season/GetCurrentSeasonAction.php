<?php

namespace App\Actions\Season;

use App\Actions\League\GetSeasonByYearAction;
use App\Data\Season\SeasonData;
use App\Models\Season;

readonly class GetCurrentSeasonAction
{
    public function __construct(private GetSeasonByYearAction $getSeasonByYearAction)
    {
    }

    public function execute(): SeasonData
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);

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

        $totalWeeks = $season->getTotalWeeks();
        $nextWeek = $lastPlayedWeek + 1;

        return min($nextWeek, $totalWeeks);
    }
}

