<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Match\SimulateWeekAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it simulates all fixtures for a specific week', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    $action = app(SimulateWeekAction::class);
    $matchesPlayed = $action->execute(1, $season->id);

    // For 4 teams, week 1 should have 2 matches
    expect($matchesPlayed)->toBe(2);

    // Verify all fixtures for week 1 are played
    $week1Fixtures = Fixture::query()
        ->where('season_id', $season->id)
        ->where('week_number', 1)
        ->get();

    expect($week1Fixtures->every(fn($fixture) => $fixture->played_at !== null))->toBeTrue()
        ->and($week1Fixtures->every(fn($fixture) => $fixture->home_score !== null))->toBeTrue()
        ->and($week1Fixtures->every(fn($fixture) => $fixture->away_score !== null))->toBeTrue();
});

test('it only simulates unplayed fixtures', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    // Manually play one fixture
    $fixture = Fixture::query()
        ->where('season_id', $season->id)
        ->where('week_number', 1)
        ->first();
    $fixture->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at'  => now(),
    ]);

    $action = app(SimulateWeekAction::class);
    $matchesPlayed = $action->execute(1, $season->id);

    // Should return 0 since all fixtures are already played
    expect($matchesPlayed)->toBe(0);
});

test('it returns zero when no fixtures exist for week', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    $action = app(SimulateWeekAction::class);
    $matchesPlayed = $action->execute(1, $season->id);

    expect($matchesPlayed)->toBe(0);
});

test('it does not simulate fixtures from other weeks', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    $action = app(SimulateWeekAction::class);
    $action->execute(1, $season->id);

    // Verify week 2 fixtures are not played
    $week2Fixtures = Fixture::query()
        ->where('season_id', $season->id)
        ->where('week_number', 2)
        ->get();

    expect($week2Fixtures->every(fn($fixture) => $fixture->played_at === null))->toBeTrue();
});
