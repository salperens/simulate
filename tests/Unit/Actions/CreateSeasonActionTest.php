<?php

use App\Actions\Season\CreateSeasonAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Season\ActiveSeasonExistsException;
use App\Exceptions\Season\SeasonYearAlreadyExistsException;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a new season with selected teams', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();
    /** @var Team $team4 */
    $team4 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);
    $seasonData = $action->execute(2025, [$team1->id, $team2->id, $team3->id, $team4->id], 'Test Season');

    expect($seasonData->year)->toBe(2025)
        ->and($seasonData->name)->toBe('Test Season')
        ->and($seasonData->status)->toBe(SeasonStatusEnum::DRAFT)
        ->and($seasonData->currentWeek)->toBe(1);

    /** @var Season|null $season */
    $season = Season::query()->find($seasonData->id);
    expect($season)->not->toBeNull()
        ->and($season->teams)->toHaveCount(4)
        ->and($season->fixtures)->not->toBeEmpty();
});

test('it generates default season name when name is not provided', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);
    $seasonData = $action->execute(2025, [$team1->id, $team2->id]);

    expect($seasonData->name)->toBe('2025-2026 Season');
});

test('it throws exception when active season exists', function () {
    // Create an active season
    Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2024,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);

    expect(fn() => $action->execute(2025, [$team1->id, $team2->id]))
        ->toThrow(ActiveSeasonExistsException::class);
});

test('it throws exception when season year already exists', function () {
    Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::DRAFT,
    ]);

    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);

    expect(fn() => $action->execute(2025, [$team1->id, $team2->id]))
        ->toThrow(SeasonYearAlreadyExistsException::class);
});

test('it creates season with correct start and end dates', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);
    $seasonData = $action->execute(2025, [$team1->id, $team2->id]);

    /** @var Season|null $season */
    $season = Season::query()->find($seasonData->id);
    expect($season->start_date->format('Y-m-d'))->toBe('2025-01-01')
        ->and($season->end_date->format('Y-m-d'))->toBe('2025-12-31');
});

test('it generates fixtures for all teams', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();
    /** @var Team $team4 */
    $team4 = Team::factory()->create();

    $action = app(CreateSeasonAction::class);
    $seasonData = $action->execute(2025, [$team1->id, $team2->id, $team3->id, $team4->id]);

    /** @var Season|null $season */
    $season = Season::query()->find($seasonData->id);
    // For 4 teams, round-robin should generate 12 fixtures (6 first half + 6 second half)
    expect($season->fixtures)->toHaveCount(12);
});
