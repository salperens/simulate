<?php

use App\Actions\Season\GetSeasonByIdAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns season data by id', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByIdAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->id)->toBe($season->id)
        ->and($seasonData->year)->toBe(2025)
        ->and($seasonData->status)->toBe(SeasonStatusEnum::ACTIVE);
});

test('it throws exception when season id does not exist', function () {
    $action = app(GetSeasonByIdAction::class);

    expect(fn() => $action->execute(99999))
        ->toThrow(SeasonNotFoundException::class);
});

test('it calculates current week correctly', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Play week 3
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 3,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(GetSeasonByIdAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->currentWeek)->toBe(3);
});

test('it returns season data with all required fields', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'name'   => 'Test Season',
        'status' => SeasonStatusEnum::DRAFT,
    ]);

    $action = app(GetSeasonByIdAction::class);
    $seasonData = $action->execute($season->id);

    expect($seasonData->id)->toBe($season->id)
        ->and($seasonData->year)->toBe(2025)
        ->and($seasonData->name)->toBe('Test Season')
        ->and($seasonData->status)->toBe(SeasonStatusEnum::DRAFT)
        ->and($seasonData->currentWeek)->toBe(1)
        ->and($seasonData->totalWeeks)->toBeGreaterThanOrEqual(0);
});
