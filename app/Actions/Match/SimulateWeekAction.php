<?php

namespace App\Actions\Match;

use App\Models\Fixture;

readonly class SimulateWeekAction
{
    public function __construct(private SimulateFixtureAction $simulateFixtureAction)
    {
    }

    public function execute(int $weekNumber, int $seasonId): int
    {
        $fixtures = Fixture::query()->where('season_id', $seasonId)
            ->where('week_number', $weekNumber)
            ->whereNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        foreach ($fixtures as $fixture) {
            $this->simulateFixtureAction->execute($fixture);
        }

        return $fixtures->count();
    }
}

