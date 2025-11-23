<?php

use App\Enums\Prediction\PredictionTypeEnum;
use App\Enums\Season\SeasonStatusEnum;
use App\MatchSimulation\Contracts\MatchSimulator;
use App\MatchSimulation\MatchContext;
use App\MatchSimulation\MatchSimulationResult;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use App\Prediction\Algorithms\MonteCarlo\FixtureSimulator;
use App\Prediction\PredictionContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it simulates remaining fixtures and returns simulated data', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create([
        'power_rating'              => 80,
        'goalkeeper_factor'         => 1.1,
        'supporter_strength'        => 1.2,
        'home_advantage_multiplier' => 1.15,
    ]);
    /** @var Team $team2 */
    $team2 = Team::factory()->create([
        'power_rating'              => 60,
        'goalkeeper_factor'         => 1.0,
        'supporter_strength'        => 1.0,
        'home_advantage_multiplier' => 1.10,
    ]);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $standings = collect();

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: $standings,
        remainingFixtures: collect([$fixture]),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $matchSimulator = new class implements MatchSimulator {
        public function simulate(MatchContext $context): MatchSimulationResult
        {
            return new MatchSimulationResult(2, 1);
        }
    };

    $simulator = new FixtureSimulator($matchSimulator);
    $result = $simulator->simulateRemaining($context);

    expect($result)->toHaveCount(1)
        ->and($result->first()->fixtureId)->toBe($fixture->id)
        ->and($result->first()->homeScore)->toBe(2)
        ->and($result->first()->awayScore)->toBe(1);
});

test('it calculates home effective power correctly', function () {
    /** @var Team $team */
    $team = Team::factory()->create([
        'power_rating'              => 100,
        'goalkeeper_factor'         => 1.2,
        'supporter_strength'        => 1.3,
        'home_advantage_multiplier' => 1.25,
    ]);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team->id]);

    /** @var Team $awayTeam */
    $awayTeam = Team::factory()->create();

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team->id,
        'away_team_id' => $awayTeam->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: collect(),
        remainingFixtures: collect([$fixture]),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $matchSimulator = new class implements MatchSimulator {
        public MatchContext $capturedContext;

        public function simulate(MatchContext $context): MatchSimulationResult
        {
            $this->capturedContext = $context;
            return new MatchSimulationResult(1, 1);
        }
    };

    $simulator = new FixtureSimulator($matchSimulator);
    $simulator->simulateRemaining($context);

    // Expected: 100 * 1.2 * 1.3 * 1.25 = 195.0
    expect($matchSimulator->capturedContext->homeEffectivePower)->toBe(195.0);
});

test('it calculates away effective power correctly', function () {
    /** @var Team $team */
    $team = Team::factory()->create([
        'power_rating'              => 100,
        'goalkeeper_factor'         => 1.2,
        'supporter_strength'        => 1.3,
        'home_advantage_multiplier' => 1.25, // Should not be used for away
    ]);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team->id]);

    /** @var Team $homeTeam */
    $homeTeam = Team::factory()->create();

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $team->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: collect(),
        remainingFixtures: collect([$fixture]),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $matchSimulator = new class implements MatchSimulator {
        public MatchContext $capturedContext;

        public function simulate(MatchContext $context): MatchSimulationResult
        {
            $this->capturedContext = $context;
            return new MatchSimulationResult(1, 1);
        }
    };

    $simulator = new FixtureSimulator($matchSimulator);
    $simulator->simulateRemaining($context);

    // Expected: 100 * 1.2 * 1.3 = 156.0 (no home advantage multiplier)
    expect($matchSimulator->capturedContext->awayEffectivePower)->toBe(156.0);
});

test('it simulates multiple fixtures', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id]);

    /** @var Fixture $fixture1 */
    $fixture1 = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    /** @var Fixture $fixture2 */
    $fixture2 = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team3->id,
        'week_number'  => 2,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $context = new PredictionContext(
        season: $season,
        currentWeek: 1,
        standings: collect(),
        remainingFixtures: collect([$fixture1, $fixture2]),
        type: PredictionTypeEnum::CHAMPIONSHIP,
    );

    $matchSimulator = new class implements MatchSimulator {
        public function simulate(MatchContext $context): MatchSimulationResult
        {
            return new MatchSimulationResult(1, 0);
        }
    };

    $simulator = new FixtureSimulator($matchSimulator);
    $result = $simulator->simulateRemaining($context);

    expect($result)->toHaveCount(2)
        ->and($result->pluck('fixtureId')->toArray())->toContain($fixture1->id, $fixture2->id);
});

