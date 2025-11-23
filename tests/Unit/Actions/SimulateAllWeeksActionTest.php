<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Match\SimulateAllWeeksAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it simulates all remaining fixtures for a season', function () {
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

    $totalFixtures = Fixture::query()->where('season_id', $season->id)->count();

    $action = app(SimulateAllWeeksAction::class);
    $matchesPlayed = $action->execute($season->id);

    expect($matchesPlayed)->toBe($totalFixtures);

    // Verify all fixtures are played
    $fixtures = Fixture::query()->where('season_id', $season->id)->get();
    expect($fixtures->every(fn($fixture) => $fixture->played_at !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->home_score !== null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->away_score !== null))->toBeTrue();
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

    $totalFixtures = Fixture::query()->where('season_id', $season->id)->count();

    $action = app(SimulateAllWeeksAction::class);
    $matchesPlayed = $action->execute($season->id);

    // Should only play remaining fixtures
    expect($matchesPlayed)->toBeLessThan($totalFixtures);

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
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // Play all fixtures first
    $action = app(SimulateAllWeeksAction::class);
    $action->execute($season->id);

    // Try to simulate all again
    $matchesPlayed = $action->execute($season->id);

    expect($matchesPlayed)->toBe(0);
});

test('it simulates fixtures in week order', function () {
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

    $action = app(SimulateAllWeeksAction::class);
    $action->execute($season->id);

    // Verify all weeks are played
    $weekNumbers = Fixture::query()
        ->where('season_id', $season->id)
        ->pluck('week_number')
        ->unique()
        ->sort()
        ->values();

    $playedWeeks = Fixture::query()
        ->where('season_id', $season->id)
        ->whereNotNull('played_at')
        ->pluck('week_number')
        ->unique()
        ->sort()
        ->values();

    expect($playedWeeks->toArray())->toBe($weekNumbers->toArray());
});

