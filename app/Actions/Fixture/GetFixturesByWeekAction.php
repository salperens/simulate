<?php

namespace App\Actions\Fixture;

use App\Data\Fixture\FixtureData;
use App\Data\Fixture\TeamData;
use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Support\Collection;

readonly class GetFixturesByWeekAction
{
    public function execute(Season $season, int $weekNumber): Collection
    {
        $fixtures = $season->fixtures()
            ->where('week_number', $weekNumber)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        return $fixtures->map(fn(Fixture $fixture) => $this->createFixtureData($fixture));
    }

    private function createFixtureData(Fixture $fixture): FixtureData
    {
        return new FixtureData(
            id: $fixture->id,
            week_number: $fixture->week_number,
            home_team: new TeamData(
                id: $fixture->homeTeam->id,
                name: $fixture->homeTeam->name,
            ),
            away_team: new TeamData(
                id: $fixture->awayTeam->id,
                name: $fixture->awayTeam->name,
            ),
            home_score: $fixture->home_score,
            away_score: $fixture->away_score,
            played_at: $fixture->played_at,
        );
    }
}

