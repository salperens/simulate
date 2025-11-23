<?php

use App\Actions\League\CalculateStandingsAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates standings correctly with win draw loss', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    $season->teams()->attach([$team1->id, $team2->id]);

    // Team 1 win 2-1
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    // Draw 1-1
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team1->id,
        'week_number'  => 2,
        'home_score'   => 1,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(CalculateStandingsAction::class);
    $standings = $action->execute($season);

    $team1Standing = $standings->firstWhere('id', $team1->id);
    $team2Standing = $standings->firstWhere('id', $team2->id);

    expect($team1Standing->played)->toBe(2)
        ->and($team1Standing->won)->toBe(1)
        ->and($team1Standing->drawn)->toBe(1)
        ->and($team1Standing->lost)->toBe(0)
        ->and($team1Standing->points)->toBe(4) // 3 for win + 1 for draw
        ->and($team1Standing->goalsFor)->toBe(3)
        ->and($team1Standing->goalsAgainst)->toBe(2)
        ->and($team1Standing->goalDifference)->toBe(1)
        ->and($team2Standing->played)->toBe(2)
        ->and($team2Standing->won)->toBe(0)
        ->and($team2Standing->drawn)->toBe(1)
        ->and($team2Standing->lost)->toBe(1)
        ->and($team2Standing->points)->toBe(1) // 1 for draw
        ->and($team2Standing->goalsFor)->toBe(2)
        ->and($team2Standing->goalsAgainst)->toBe(3)
        ->and($team2Standing->goalDifference)->toBe(-1);
});

test('it sorts standings by points then goal difference then goals for', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);
    /** @var Team $team3 */
    $team3 = Team::factory()->create(['name' => 'Team C']);

    $season->teams()->attach([$team1->id, $team2->id, $team3->id]);

    // Team 1: 6 points, GD +3, GF 5
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 0,
        'played_at'    => now(),
    ]);
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team3->id,
        'week_number'  => 2,
        'home_score'   => 2,
        'away_score'   => 2,
        'played_at'    => now(),
    ]);

    // Team 2: 6 points, GD +3, GF 4
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team3->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 0,
        'played_at'    => now(),
    ]);

    // Team 3: 1 point
    // Already played above

    $action = app(CalculateStandingsAction::class);
    $standings = $action->execute($season);

    expect($standings->first()->id)->toBe($team1->id) // Team 1 first (more goals for)
    ->and($standings->get(1)->id)->toBe($team2->id) // Team 2 second
    ->and($standings->last()->id)->toBe($team3->id); // Team 3 last
});

test('it calculates points correctly: 3 for win, 1 for draw, 0 for loss', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    $season->teams()->attach([$team1->id, $team2->id]);

    // Win
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 0,
        'played_at'    => now(),
    ]);

    // Draw
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 2,
        'home_score'   => 1,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    // Loss
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team2->id,
        'away_team_id' => $team1->id,
        'week_number'  => 3,
        'home_score'   => 3,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(CalculateStandingsAction::class);
    $standings = $action->execute($season);

    $team1Standing = $standings->firstWhere('id', $team1->id);

    expect($team1Standing->points)->toBe(4) // 3 (win) + 1 (draw) + 0 (loss)
    ->and($team1Standing->won)->toBe(1)
        ->and($team1Standing->drawn)->toBe(1)
        ->and($team1Standing->lost)->toBe(1);
});
