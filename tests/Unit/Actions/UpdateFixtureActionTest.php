<?php

use App\Actions\Fixture\UpdateFixtureAction;
use App\Actions\League\CalculateStandingsAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it updates fixture scores and recalculates standings', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year' => 2025,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    $season->teams()->attach([$team1->id, $team2->id]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id' => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number' => 1,
        'home_score' => 1,
        'away_score' => 0,
        'played_at' => now(),
    ]);

    $action = app(UpdateFixtureAction::class);
    $updatedFixture = $action->execute($fixture->id, 2, 1);

    expect($updatedFixture->homeScore)->toBe(2)
        ->and($updatedFixture->awayScore)->toBe(1);

    // Verify standings are recalculated
    $standingsAction = app(CalculateStandingsAction::class);
    $standings = $standingsAction->execute($season);

    $team1Standing = $standings->firstWhere('id', $team1->id);
    expect($team1Standing->goalsFor)->toBe(2)
        ->and($team1Standing->goalsAgainst)->toBe(1);
});

test('it recalculates standings correctly after score update', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year' => 2025,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    $season->teams()->attach([$team1->id, $team2->id]);

    // Initial: Team 1 wins 1-0
    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id' => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number' => 1,
        'home_score' => 1,
        'away_score' => 0,
        'played_at' => now(),
    ]);

    // Update to draw 1-1
    $action = app(UpdateFixtureAction::class);
    $action->execute($fixture->id, 1, 1);

    $standingsAction = app(CalculateStandingsAction::class);
    $standings = $standingsAction->execute($season);

    $team1Standing = $standings->firstWhere('id', $team1->id);
    $team2Standing = $standings->firstWhere('id', $team2->id);

    expect($team1Standing->points)->toBe(1) // Changed from 3 to 1
        ->and($team1Standing->won)->toBe(0)
        ->and($team1Standing->drawn)->toBe(1)
        ->and($team2Standing->points)->toBe(1)
        ->and($team2Standing->won)->toBe(0)
        ->and($team2Standing->drawn)->toBe(1);
});
