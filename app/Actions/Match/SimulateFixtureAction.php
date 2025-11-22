<?php

namespace App\Actions\Match;

use App\Exceptions\Match\FixtureAlreadyPlayedException;
use App\Exceptions\Match\InvalidFixtureException;
use App\MatchSimulation\Contracts\MatchSimulator;
use App\MatchSimulation\MatchContext;
use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Support\Carbon;

readonly class SimulateFixtureAction
{
    public function __construct(private MatchSimulator $matchSimulator)
    {
    }

    public function execute(Fixture $fixture): void
    {
        if ($fixture->played_at !== null) {
            throw FixtureAlreadyPlayedException::create();
        }

        $fixture->loadMissing(['homeTeam', 'awayTeam']);

        $homeTeam = $fixture->homeTeam;
        $awayTeam = $fixture->awayTeam;

        if ($homeTeam === null || $awayTeam === null) {
            throw InvalidFixtureException::missingTeams();
        }

        $homeEffectivePower = $this->calculateHomeEffectivePower($homeTeam);
        $awayEffectivePower = $this->calculateAwayEffectivePower($awayTeam);

        $context = new MatchContext(
            homeTeamId: $homeTeam->id,
            awayTeamId: $awayTeam->id,
            homeEffectivePower: $homeEffectivePower,
            awayEffectivePower: $awayEffectivePower,
            week: $fixture->week_number,
            seasonId: $fixture->season_id,
        );

        $result = $this->matchSimulator->simulate($context);

        $fixture->update([
            'home_score' => $result->homeGoals,
            'away_score' => $result->awayGoals,
            'played_at'  => Carbon::now(),
        ]);
    }

    private function calculateHomeEffectivePower(Team $team): float
    {
        return (float)$team->power_rating
            * $team->goalkeeper_factor
            * $team->supporter_strength
            * $team->home_advantage_multiplier;
    }

    private function calculateAwayEffectivePower(Team $team): float
    {
        return (float)$team->power_rating
            * $team->goalkeeper_factor
            * $team->supporter_strength;
    }
}

