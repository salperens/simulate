<?php

namespace App\Actions\Season;

use App\Actions\Fixture\GenerateFixturesAction;
use App\Data\Season\SeasonData;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\ActiveSeasonExistsException;
use App\Exceptions\Season\SeasonYearAlreadyExistsException;
use App\Models\Season;
use Illuminate\Support\Carbon;

readonly class CreateSeasonAction
{
    public function __construct(private GenerateFixturesAction $generateFixturesAction)
    {
    }

    /**
     * Create a new season with selected teams.
     *
     * @param int $year
     * @param array<int> $teamIds
     * @param string|null $name
     * @return SeasonData
     * @throws ActiveSeasonExistsException
     * @throws SeasonYearAlreadyExistsException
     */
    public function execute(int $year, array $teamIds, ?string $name = null): SeasonData
    {
        $this->validateNoActiveSeason();
        $this->validateYearNotExists($year);

        $seasonName = $name ?? "{$year}-" . ($year + 1) . " Season";

        $season = Season::create([
            'year'       => $year,
            'name'       => $seasonName,
            'status'     => SeasonStatusEnum::DRAFT,
            'start_date' => Carbon::create($year, 1, 1)->startOfYear(),
            'end_date'   => Carbon::create($year, 12, 31)->endOfYear(),
        ]);

        $season->teams()->sync($teamIds);

        $this->generateFixturesAction->execute($season);

        return $this->createSeasonData($season);
    }

    private function validateNoActiveSeason(): void
    {
        $activeSeason = Season::where('status', SeasonStatusEnum::ACTIVE)->first();

        if ($activeSeason !== null) {
            throw ActiveSeasonExistsException::create($activeSeason);
        }
    }

    private function validateYearNotExists(int $year): void
    {
        $existingSeason = Season::where('year', $year)->first();

        if ($existingSeason !== null) {
            throw SeasonYearAlreadyExistsException::create($year);
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
