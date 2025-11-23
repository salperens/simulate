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
            weekNumber: $fixture->week_number,
            homeTeam: new TeamData(
                id: $fixture->homeTeam->id,
                name: $fixture->homeTeam->name,
            ),
            awayTeam: new TeamData(
                id: $fixture->awayTeam->id,
                name: $fixture->awayTeam->name,
            ),
            homeScore: $fixture->home_score,
            awayScore: $fixture->away_score,
            playedAt: $fixture->played_at,
        );
    }
}

