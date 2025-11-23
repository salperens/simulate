<?php

namespace App\Actions\League;

use App\Data\League\TeamStandingData;
use App\Data\League\TeamStatsData;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Collection;

readonly class CalculateStandingsAction
{
    /**
     * Calculate standings for a season.
     * If week is provided, only include fixtures up to and including that week.
     *
     * @param Season $season
     * @param int|null $week Optional week number to calculate standings up to
     * @return Collection<TeamStandingData>
     */
    public function execute(Season $season, ?int $week = null): Collection
    {
        $playedFixtures = $this->getPlayedFixtures($season, $week);

        $teamStandings = $season->teams
            ->map(fn(Team $team) => $this->calculateTeamStats($team, $playedFixtures))
            ->pipe(fn(Collection $standings) => $this->sortStandings($standings));

        return new Collection($teamStandings);
    }

    /**
     * Get played fixtures for a season.
     * If week is provided, only include fixtures up to and including that week.
     *
     * @param Season $season
     * @param int|null $week Optional week number
     * @return Collection<Fixture>
     */
    private function getPlayedFixtures(Season $season, ?int $week = null): Collection
    {
        $query = $season->fixtures()
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam']);

        if ($week !== null) {
            $query->where('week_number', '<=', $week);
        }

        return $query->get();
    }

    private function calculateTeamStats(Team $team, Collection $fixtures): TeamStandingData
    {
        $teamFixtures = $this->getTeamFixtures($team, $fixtures);

        $stats = $teamFixtures->reduce(
            fn(TeamStatsData $stats, Fixture $fixture) => $this->processFixture($team, $fixture, $stats),
            new TeamStatsData()
        );

        return $this->createTeamStandingData($team, $stats);
    }

    private function getTeamFixtures(Team $team, Collection $fixtures): Collection
    {
        return $fixtures->filter(function (Fixture $fixture) use ($team) {
            return $this->isTeamInFixture($team, $fixture) && $this->isFixturePlayed($fixture);
        });
    }

    private function isTeamInFixture(Team $team, Fixture $fixture): bool
    {
        return $fixture->home_team_id === $team->id || $fixture->away_team_id === $team->id;
    }

    private function isFixturePlayed(Fixture $fixture): bool
    {
        return $fixture->home_score !== null && $fixture->away_score !== null;
    }

    private function processFixture(Team $team, Fixture $fixture, TeamStatsData $stats): TeamStatsData
    {
        $isHomeTeam = $fixture->home_team_id === $team->id;
        $stats = $stats->incrementPlayed();

        if ($isHomeTeam) {
            return $this->processHomeFixture($fixture, $stats);
        }

        return $this->processAwayFixture($fixture, $stats);
    }

    private function processHomeFixture(Fixture $fixture, TeamStatsData $stats): TeamStatsData
    {
        $stats = $stats->addGoals($fixture->home_score, $fixture->away_score);

        return $this->updateMatchResult($fixture->home_score, $fixture->away_score, $stats);
    }

    private function processAwayFixture(Fixture $fixture, TeamStatsData $stats): TeamStatsData
    {
        $stats = $stats->addGoals($fixture->away_score, $fixture->home_score);

        return $this->updateMatchResult($fixture->away_score, $fixture->home_score, $stats);
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

    private function createTeamStandingData(Team $team, TeamStatsData $stats): TeamStandingData
    {
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

