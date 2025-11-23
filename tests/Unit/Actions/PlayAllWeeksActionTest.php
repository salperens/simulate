<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\League\PlayAllWeeksAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it plays all remaining fixtures for a season', function () {
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

    $totalFixtures = Fixture::query()->where('season_id', $season->id)->count();

    $action = app(PlayAllWeeksAction::class);
    $result = $action->execute($season);

    expect($result->matchesPlayed)->toBe($totalFixtures);

    // Verify all fixtures are played
    $fixtures = Fixture::query()->where('season_id', $season->id)->get();
    expect($fixtures->every(fn($fixture) => $fixture->played_at !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->home_score !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->away_score !== null))->toBeTrue();
});

test('it only plays unplayed fixtures', function () {
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

    // Play week 1 manually
    /** @var Fixture|null $week1Fixture */
    $week1Fixture = Fixture::query()->where('season_id', $season->id)
        ->where('week_number', 1)
        ->first();
    $week1Fixture->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at' => now(),
    ]);

    $action = app(PlayAllWeeksAction::class);
    $result = $action->execute($season);

    // Should only play remaining fixtures
    $totalFixtures = Fixture::query()->where('season_id', $season->id)->count();
    expect($result->matchesPlayed)->toBeLessThan($totalFixtures);

    // Verify all fixtures are still played
    $fixtures = Fixture::query()->where('season_id', $season->id)->get();
    expect($fixtures->every(fn($fixture) => $fixture->played_at !== null))->toBeTrue();
});

test('it returns zero when all fixtures are already played', function () {
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

    // Play all fixtures first
    $action = app(PlayAllWeeksAction::class);
    $action->execute($season);

    // Try to play all again
    $result = $action->execute($season);

    expect($result->matchesPlayed)->toBe(0);
});

