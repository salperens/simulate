<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Season\GetAllSeasonsAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns all seasons ordered by year descending', function () {
    Season::factory()->create(['year' => 2023]);
    Season::factory()->create(['year' => 2025]);
    Season::factory()->create(['year' => 2024]);

    $action = app(GetAllSeasonsAction::class);
    $seasons = $action->execute();

    expect($seasons)->toHaveCount(3)
        ->and($seasons->first()->year)->toBe(2025)
        ->and($seasons->get(1)->year)->toBe(2024)
        ->and($seasons->last()->year)->toBe(2023);
});

test('it returns empty collection when no seasons exist', function () {
    $action = app(GetAllSeasonsAction::class);
    $seasons = $action->execute();

    expect($seasons)->toHaveCount(0);
});

test('it calculates current week correctly for each season', function () {
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

    $action = app(GetAllSeasonsAction::class);
    $seasons = $action->execute();

    $foundSeason = $seasons->firstWhere('id', $season->id);
    expect($foundSeason->currentWeek)->toBe(2);
});

test('it returns season data with all required fields', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'name'   => 'Test Season',
        'status' => SeasonStatusEnum::DRAFT,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Generate fixtures
    app(GenerateFixturesAction::class)->execute($season);

    $action = app(GetAllSeasonsAction::class);
    $seasons = $action->execute();

    $foundSeason = $seasons->firstWhere('id', $season->id);
    expect($foundSeason->year)->toBe(2025)
        ->and($foundSeason->name)->toBe('Test Season')
        ->and($foundSeason->status)->toBe(SeasonStatusEnum::DRAFT)
        ->and($foundSeason->currentWeek)->toBe(1)
        ->and($foundSeason->totalWeeks)->toBeGreaterThan(0);
});

