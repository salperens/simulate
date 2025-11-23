<?php

namespace App\Actions\Match;

use App\Models\Fixture;

readonly class SimulateAllWeeksAction
{
    public function __construct(private SimulateFixtureAction $simulateFixtureAction)
    {
    }

    public function execute(int $seasonId): int
    {
        $fixtures = Fixture::query()->where('season_id', $seasonId)
            ->whereNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week_number')
            ->get();

        foreach ($fixtures as $fixture) {
            $this->simulateFixtureAction->execute($fixture);
        }

        return $fixtures->count();
    }
}

