<?php

namespace App\Prediction\Algorithms\MonteCarlo;

use App\Data\Prediction\SimulatedFixtureData;
use App\MatchSimulation\Contracts\MatchSimulator;
use App\MatchSimulation\MatchContext;
use App\Models\Fixture;
use App\Models\Team;
use App\Prediction\PredictionContext;
use Illuminate\Support\Collection;

readonly class FixtureSimulator
{
    public function __construct(private MatchSimulator $matchSimulator)
    {
    }

    /**
     * @return Collection<int, SimulatedFixtureData>
     */
    public function simulateRemaining(PredictionContext $context): Collection
    {
        return $context->remainingFixtures->map(function (Fixture $fixture) use ($context) {
            $homeTeam = $fixture->homeTeam;
            $awayTeam = $fixture->awayTeam;

            $homeEffectivePower = $this->calculateHomeEffectivePower($homeTeam);
            $awayEffectivePower = $this->calculateAwayEffectivePower($awayTeam);

            $matchContext = new MatchContext(
                homeTeamId: $homeTeam->id,
                awayTeamId: $awayTeam->id,
                homeEffectivePower: $homeEffectivePower,
                awayEffectivePower: $awayEffectivePower,
                week: $fixture->week_number,
                seasonId: $context->season->id,
            );

            $result = $this->matchSimulator->simulate($matchContext);

            return new SimulatedFixtureData(
                fixtureId: $fixture->id,
                homeScore: $result->homeGoals,
                awayScore: $result->awayGoals,
            );
        });
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
