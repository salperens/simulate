<?php

namespace App\Actions\Fixture;

use App\Actions\League\CalculateStandingsAction;
use App\Actions\Prediction\CalculatePredictionsIfApplicableAction;
use App\Data\Fixture\FixtureData;
use App\Data\Fixture\TeamData;
use App\Exceptions\Fixture\FixtureNotFoundException;
use App\Exceptions\Fixture\FixtureNotPlayedException;
use App\Models\ChampionshipPrediction;
use App\Models\Fixture;
use App\Models\Season;

readonly class UpdateFixtureAction
{
    public function __construct(
        private CalculateStandingsAction               $calculateStandingsAction,
        private CalculatePredictionsIfApplicableAction $calculatePredictionsIfApplicableAction,
    )
    {
    }

    /**
     * Update fixture result and recalculate standings and predictions.
     *
     * @param int $fixtureId
     * @param int $homeScore
     * @param int $awayScore
     * @return FixtureData
     * @throws FixtureNotFoundException
     */
    public function execute(int $fixtureId, int $homeScore, int $awayScore): FixtureData
    {
        $fixture = Fixture::with(['season.teams', 'homeTeam', 'awayTeam'])->find($fixtureId);

        if ($fixture === null) {
            throw FixtureNotFoundException::create($fixtureId);
        }

        if ($fixture->played_at === null) {
            throw FixtureNotPlayedException::create();
        }

        $fixture->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
        ]);

        if (!$fixture->season->relationLoaded('teams')) {
            $fixture->season->load('teams');
        }

        $this->recalculateStandings($fixture->season);
        $this->recalculatePredictions($fixture->season, $fixture->week_number);

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

    private function recalculatePredictions(Season $season, int $weekNumber): void
    {
        $totalWeeks = $season->getTotalWeeks();
        $lastThreeWeeksStart = max(1, $totalWeeks - 2);

        if ($weekNumber < $lastThreeWeeksStart) {
            return;
        }

        ChampionshipPrediction::query()
            ->where('season_id', $season->id)
            ->where('week_number', '>=', $weekNumber)
            ->where('week_number', '>=', $lastThreeWeeksStart)
            ->delete();

        $startWeek = max($weekNumber, $lastThreeWeeksStart);
        for ($week = $startWeek; $week <= $totalWeeks; $week++) {
            $this->calculatePredictionsIfApplicableAction->execute($season, $week);
        }
    }
}
