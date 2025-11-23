<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\League\TeamStandingData;
use App\Data\League\TeamStatsData;
use App\Data\Prediction\FixtureResultData;
use App\Data\Prediction\SimulatedFixtureData;
use App\Models\Fixture;
use App\Models\Team;
use App\Prediction\PredictionContext;
use Illuminate\Support\Collection;

readonly class StandingsCalculator
{
    /**
     * @param PredictionContext $context
     * @param Collection<int, SimulatedFixtureData> $simulatedFixtures
     * @return Collection<int, TeamStandingData>
     */
    public function calculate(PredictionContext $context, Collection $simulatedFixtures): Collection
    {
        if (!$context->season->relationLoaded('teams')) {
            $context->season->load('teams');
        }

        $allFixtures = $this->mergeFixtures($context, $simulatedFixtures);
        $teams = $context->season->teams;

        return $teams->map(function (Team $team) use ($allFixtures) {
            return $this->calculateTeamStats($team, $allFixtures);
        })->pipe(fn(Collection $standings) => $this->sortStandings($standings));
    }

    /**
     * @param PredictionContext $context
     * @param Collection<int, SimulatedFixtureData> $simulatedFixtures
     * @return Collection<int, FixtureResultData>
     */
    private function mergeFixtures(PredictionContext $context, Collection $simulatedFixtures): Collection
    {
        $playedFixtures = $context->season->fixtures()
            ->whereNotNull('played_at')
            ->get()
            ->map(fn(Fixture $f) => new FixtureResultData(
                homeTeamId: $f->home_team_id,
                awayTeamId: $f->away_team_id,
                homeScore: $f->home_score,
                awayScore: $f->away_score,
            ));

        $simulated = $simulatedFixtures->map(function (SimulatedFixtureData $sim) use ($context) {
            $fixture = $context->remainingFixtures->firstWhere('id', $sim->fixtureId);
            return new FixtureResultData(
                homeTeamId: $fixture->home_team_id,
                awayTeamId: $fixture->away_team_id,
                homeScore: $sim->homeScore,
                awayScore: $sim->awayScore,
            );
        });

        return collect(array_merge($playedFixtures->all(), $simulated->all()));
    }

    private function calculateTeamStats(Team $team, Collection $allFixtures): TeamStandingData
    {
        $teamFixtures = $allFixtures->filter(function (FixtureResultData $fixture) use ($team) {
            return $fixture->homeTeamId === $team->id || $fixture->awayTeamId === $team->id;
        });

        /** @var TeamStatsData $stats */
        $stats = $teamFixtures->reduce(
            fn(TeamStatsData $stats, FixtureResultData $fixture) => $this->processFixture($team, $fixture, $stats),
            new TeamStatsData(),
        );

        return new TeamStandingData(
            id: $team->id,
            name: $team->name,
            played: $stats->played,
            won: $stats->won,
            drawn: $stats->drawn,
            lost: $stats->lost,
            goalsFor: $stats->goalsFor,
            goalsAgainst: $stats->goalsAgainst,
            goalDifference: $stats->getGoalDifference(),
            points: $stats->getPoints(),
        );
    }

    private function processFixture(Team $team, FixtureResultData $fixture, TeamStatsData $stats): TeamStatsData
    {
        $isHomeTeam = $fixture->homeTeamId === $team->id;
        $stats = $stats->incrementPlayed();

        if ($isHomeTeam) {
            $stats = $stats->addGoals($fixture->homeScore, $fixture->awayScore);
            return $this->updateMatchResult($fixture->homeScore, $fixture->awayScore, $stats);
        }

        $stats = $stats->addGoals($fixture->awayScore, $fixture->homeScore);
        return $this->updateMatchResult($fixture->awayScore, $fixture->homeScore, $stats);
    }

    private function updateMatchResult(int $teamScore, int $opponentScore, TeamStatsData $stats): TeamStatsData
    {
        if ($teamScore > $opponentScore) {
            return $stats->incrementWon();
        }

        if ($teamScore === $opponentScore) {
            return $stats->incrementDrawn();
        }

        return $stats->incrementLost();
    }

    /**
     * @param Collection<int, TeamStandingData> $standings
     * @return Collection<int, TeamStandingData>
     */
    private function sortStandings(Collection $standings): Collection
    {
        return $standings->sort(function (TeamStandingData $a, TeamStandingData $b) {
            if ($a->points !== $b->points) {
                return $b->points <=> $a->points;
            }

            if ($a->goalDifference !== $b->goalDifference) {
                return $b->goalDifference <=> $a->goalDifference;
            }

            return $b->goalsFor <=> $a->goalsFor;
        })->values();
    }
}
