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
     * @param Season $season
     * @return Collection<TeamStandingData>
     */
    public function execute(Season $season): Collection
    {
        $playedFixtures = $this->getPlayedFixtures($season);

        $teamStandings = $season->teams
            ->map(fn(Team $team) => $this->calculateTeamStats($team, $playedFixtures))
            ->pipe(fn(Collection $standings) => $this->sortStandings($standings));

        return new Collection($teamStandings);
    }

    private function getPlayedFixtures(Season $season): Collection
    {
        return $season->fixtures()
            ->whereNotNull('played_at')
            ->with(['homeTeam', 'awayTeam'])
            ->get();
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
            goals_for: $stats->goals_for,
            goals_against: $stats->goals_against,
            goal_difference: $stats->getGoalDifference(),
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

            if ($a->goal_difference !== $b->goal_difference) {
                return $b->goal_difference <=> $a->goal_difference;
            }

            return $b->goals_for <=> $a->goals_for;
        })->values();
    }
}

