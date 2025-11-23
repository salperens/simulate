<?php

use App\Actions\Season\GetCurrentSeasonAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns current season by current year', function () {
    $currentYear = now()->year;

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => $currentYear,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetCurrentSeasonAction::class);
    $seasonData = $action->execute();

    expect($seasonData->id)->toBe($season->id)
        ->and($seasonData->year)->toBe($currentYear);
});

test('it throws exception when current season does not exist', function () {
    $action = app(GetCurrentSeasonAction::class);

    expect(fn() => $action->execute())
        ->toThrow(SeasonNotFoundException::class);
});

test('it calculates current week correctly', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $currentYear = now()->year;

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => $currentYear,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Play week 2
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 2,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(GetCurrentSeasonAction::class);
    $seasonData = $action->execute();

    expect($seasonData->currentWeek)->toBe(2);
});

test('it returns season data with all required fields', function () {
    $currentYear = now()->year;

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => $currentYear,
        'name'   => 'Test Season',
        'status' => SeasonStatusEnum::DRAFT,
    ]);

    $action = app(GetCurrentSeasonAction::class);
    $seasonData = $action->execute();

    expect($seasonData->year)->toBe($currentYear)
        ->and($seasonData->name)->toBe('Test Season')
        ->and($seasonData->status)->toBe(SeasonStatusEnum::DRAFT)
        ->and($seasonData->currentWeek)->toBe(1)
        ->and($seasonData->totalWeeks)->toBeGreaterThanOrEqual(0);
});
