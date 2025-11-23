<?php

namespace App\Actions\Season;

use App\Data\Season\SeasonData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\CannotCompleteSeasonException;
use App\Models\Season;

readonly class CompleteSeasonAction
{
    public function execute(int $seasonId): SeasonData
    {
        $season = Season::findOrFail($seasonId);

        if ($season->status !== SeasonStatusEnum::ACTIVE) {
            throw CannotCompleteSeasonException::notActive();
        }

        $totalWeeks = $season->getTotalWeeks();
        $lastPlayedWeek = $season->fixtures()
            ->whereNotNull('played_at')
            ->max('week_number');

        if ($lastPlayedWeek < $totalWeeks) {
            throw CannotCompleteSeasonException::notAllMatchesPlayed();
        }

        $season->update([
            'status' => SeasonStatusEnum::COMPLETED,
        ]);

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
