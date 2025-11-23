<?php

use App\Actions\Season\CompleteSeasonAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\CannotCompleteSeasonException;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it completes an active season when all matches are played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Create and play all fixtures
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(CompleteSeasonAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->status)->toBe(SeasonStatusEnum::COMPLETED);

    $season->refresh();
    expect($season->status)->toBe(SeasonStatusEnum::COMPLETED);
});

test('it throws exception when trying to complete non-active season', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);

    $action = app(CompleteSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(CannotCompleteSeasonException::class);
});

test('it throws exception when trying to complete completed season', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::COMPLETED,
        'year'   => 2025,
    ]);

    $action = app(CompleteSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(CannotCompleteSeasonException::class);
});

test('it throws exception when not all matches are played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();
    /** @var Team $team4 */
    $team4 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    // Create fixtures but don't play all of them
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    // Leave some fixtures unplayed
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team3->id,
        'away_team_id' => $team4->id,
        'week_number'  => 2,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    $action = app(CompleteSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(CannotCompleteSeasonException::class);
});

test('it calculates current week correctly when completing season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Play all fixtures
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(CompleteSeasonAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->currentWeek)->toBe(1);
});

