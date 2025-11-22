<?php

namespace App\Actions\Fixture;

use App\Exceptions\Fixture\CannotGenerateFixturesException;
use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateFixturesAction
{
    public function execute(Season $season): Collection
    {
        $teams = $season->teams;

        if ($teams->isEmpty()) {
            throw CannotGenerateFixturesException::noTeams();
        }

        $teamIds = $teams->pluck('id')->toArray();
        $teamIds = $this->normalizeTeamCount($teamIds);
        $totalWeeks = count($teamIds) - 1;

        $fixtures = collect();

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $fixtures = $fixtures->merge(
                $this->generateWeekFixtures($season, $teamIds, $week, false)
            );
        }

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $fixtures = $fixtures->merge(
                $this->generateWeekFixtures($season, $teamIds, $week + $totalWeeks, true)
            );
        }

        $this->persistFixtures($fixtures);

        return $season->fresh()->fixtures;
    }

    private function normalizeTeamCount(array $teamIds): array
    {
        if (count($teamIds) % 2 !== 0) {
            $teamIds[] = null;
        }

        return $teamIds;
    }

    private function generateWeekFixtures(
        Season $season,
        array  $teamIds,
        int    $weekNumber,
        bool   $reverseHomeAway,
    ): Collection
    {
        $rotatedTeams = $this->rotateTeams($teamIds, $weekNumber - 1);
        $fixtures = collect();

        for ($i = 0; $i < count($rotatedTeams) / 2; $i++) {
            $homeTeamId = $rotatedTeams[$i];
            $awayTeamId = $rotatedTeams[count($rotatedTeams) - 1 - $i];

            if ($homeTeamId === null || $awayTeamId === null) {
                continue;
            }

            if ($reverseHomeAway) {
                [$homeTeamId, $awayTeamId] = [$awayTeamId, $homeTeamId];
            }

            $fixtures->push($this->createFixtureData($season->id, $weekNumber, $homeTeamId, $awayTeamId));
        }

        return $fixtures;
    }

    private function rotateTeams(array $teams, int $rotations): array
    {
        if (count($teams) <= 1) {
            return $teams;
        }

        $first = array_shift($teams);
        $rotations = $rotations % (count($teams));
        $rotatedRest = array_merge(
            array_slice($teams, $rotations),
            array_slice($teams, 0, $rotations)
        );

        return array_merge([$first], $rotatedRest);
    }

    private function createFixtureData(int $seasonId, int $weekNumber, int $homeTeamId, int $awayTeamId): array
    {
        return [
            'season_id'    => $seasonId,
            'week_number'  => $weekNumber,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'home_score'   => null,
            'away_score'   => null,
            'played_at'    => null,
        ];
    }

    private function persistFixtures(Collection $fixtures): void
    {
        DB::transaction(function () use ($fixtures) {
            $fixtures->each(function (array $fixture) {
                if ($fixture['home_team_id'] !== null && $fixture['away_team_id'] !== null) {
                    Fixture::create($fixture);
                }
            });
        });
    }
}
