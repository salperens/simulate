<?php

namespace App\Actions\Season;

use App\Data\Season\SeasonData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\ActiveSeasonExistsException;
use App\Exceptions\Season\CannotStartSeasonException;
use App\Models\Season;

readonly class StartSeasonAction
{
    public function execute(int $seasonId): SeasonData
    {
        $this->validateNoActiveSeason();

        $season = Season::findOrFail($seasonId);

        if ($season->status !== SeasonStatusEnum::DRAFT) {
            throw CannotStartSeasonException::notDraft();
        }

        $season->update([
            'status' => SeasonStatusEnum::ACTIVE,
        ]);

        return $this->createSeasonData($season);
    }

    private function validateNoActiveSeason(): void
    {
        $activeSeason = Season::where('status', SeasonStatusEnum::ACTIVE)->first();

        if ($activeSeason !== null) {
            throw ActiveSeasonExistsException::create($activeSeason);
        }
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
