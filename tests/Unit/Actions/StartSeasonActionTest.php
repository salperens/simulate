<?php

use App\Actions\Season\StartSeasonAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\ActiveSeasonExistsException;
use App\Exceptions\Season\CannotStartSeasonException;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it starts a draft season and changes status to active', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year' => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $action = app(StartSeasonAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->status)->toBe(SeasonStatusEnum::ACTIVE);

    $season->refresh();
    expect($season->status)->toBe(SeasonStatusEnum::ACTIVE);
});

test('it throws exception when trying to start non-draft season', function () {
    // Create a completed season (not active, so validateNoActiveSeason passes)
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::COMPLETED,
        'year' => 2025,
    ]);

    $action = app(StartSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(CannotStartSeasonException::class);
});

test('it throws exception when trying to start completed season', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::COMPLETED,
        'year' => 2025,
    ]);

    $action = app(StartSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(CannotStartSeasonException::class);
});

test('it throws exception when active season already exists', function () {
    // Create an existing active season
    Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year' => 2024,
    ]);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year' => 2025,
    ]);

    $action = app(StartSeasonAction::class);

    expect(fn() => $action->execute($season->id))
        ->toThrow(ActiveSeasonExistsException::class);
});

test('it calculates current week correctly when starting season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year' => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $action = app(StartSeasonAction::class);
    $seasonData = $action->execute($season->id);

    // No matches played yet, should be week 1
    expect($seasonData->currentWeek)->toBe(1);
});

