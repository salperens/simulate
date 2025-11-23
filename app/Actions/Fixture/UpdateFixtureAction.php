<?php

namespace App\Actions\Fixture;

use App\Actions\League\CalculateStandingsAction;
use App\Data\Fixture\FixtureData;
use App\Data\Fixture\TeamData;
use App\Exceptions\Fixture\FixtureNotFoundException;
use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Support\Carbon;

readonly class UpdateFixtureAction
{
    public function __construct(private CalculateStandingsAction $calculateStandingsAction)
    {
    }

    /**
     * Update fixture result and recalculate standings.
     *
     * @param int $fixtureId
     * @param int $homeScore
     * @param int $awayScore
     * @return FixtureData
     * @throws FixtureNotFoundException
     */
    public function execute(int $fixtureId, int $homeScore, int $awayScore): FixtureData
    {
        $fixture = Fixture::with(['season', 'homeTeam', 'awayTeam'])->find($fixtureId);

        if ($fixture === null) {
            throw FixtureNotFoundException::create($fixtureId);
        }

        $fixture->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'played_at'  => $fixture->played_at ?? Carbon::now(),
        ]);

        $this->recalculateStandings($fixture->season);

        $fixture->refresh();

        return $this->createFixtureData($fixture);
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

    private function recalculateStandings(Season $season): void
    {
        $this->calculateStandingsAction->execute($season);
    }
}
