<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\League\PlayWeekAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it plays all fixtures for a specific week', function () {
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
        'year' => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    // Generate fixtures
    app(GenerateFixturesAction::class)->execute($season);

    $action = app(PlayWeekAction::class);
    $result = $action->execute($season, 1);

    expect($result->week)->toBe(1)
        ->and($result->matchesPlayed)->toBeGreaterThan(0);

    // Verify fixtures are played
    $fixtures = Fixture::query()->where('season_id', $season->id)
        ->where('week_number', 1)
        ->get();

    expect($fixtures->every(fn($fixture) => $fixture->played_at !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->home_score !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->away_score !== null))->toBeTrue();
});

test('it only plays fixtures for the specified week', function () {
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
        'year' => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    // Generate fixtures
    app(GenerateFixturesAction::class)->execute($season);

    $action = app(PlayWeekAction::class);
    $action->execute($season, 1);

    // Verify week 2 fixtures are not played
    $week2Fixtures = Fixture::query()->where('season_id', $season->id)
        ->where('week_number', 2)
        ->get();

    expect($week2Fixtures->every(fn($fixture) => $fixture->played_at === null))->toBeTrue();
});

test('it returns correct number of matches played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year' => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Generate fixtures
    app(GenerateFixturesAction::class)->execute($season);

    $action = app(PlayWeekAction::class);
    $result = $action->execute($season, 1);

    // For 2 teams, there should be 1 match per week
    expect($result->matchesPlayed)->toBe(1);
});
