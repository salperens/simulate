<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Fixture\CannotGenerateFixturesException;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it generates fixtures for even number of teams', function () {
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
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    $action = app(GenerateFixturesAction::class);
    $fixtures = $action->execute($season);

    // For 4 teams: 3 weeks first half + 3 weeks second half = 6 weeks
    // Each week has 2 matches, total = 12 fixtures
    expect($fixtures)->toHaveCount(12);

    // Verify all fixtures are created
    $dbFixtures = Fixture::query()->where('season_id', $season->id)->get();
    expect($dbFixtures)->toHaveCount(12);
});

test('it generates fixtures for odd number of teams', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id]);

    $action = app(GenerateFixturesAction::class);
    $fixtures = $action->execute($season);

    // For 3 teams (normalized to 4 with null): 3 weeks first half + 3 weeks second half = 6 weeks
    // But only 3 teams play, so each week has 1 match, total = 6 fixtures
    expect($fixtures)->toHaveCount(6);

    // Verify all fixtures are created
    $dbFixtures = Fixture::query()->where('season_id', $season->id)->get();
    expect($dbFixtures)->toHaveCount(6);
});

test('it throws exception when season has no teams', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);

    $action = app(GenerateFixturesAction::class);

    expect(fn() => $action->execute($season))
        ->toThrow(CannotGenerateFixturesException::class);
});

test('it generates round-robin fixtures with home and away matches', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $action = app(GenerateFixturesAction::class);
    $fixtures = $action->execute($season);

    // For 2 teams: 1 week first half + 1 week second half = 2 fixtures
    expect($fixtures)->toHaveCount(2);

    // Verify home and away matches exist
    $homeMatch = Fixture::query()
        ->where('season_id', $season->id)
        ->where('home_team_id', $team1->id)
        ->where('away_team_id', $team2->id)
        ->first();

    $awayMatch = Fixture::query()
        ->where('season_id', $season->id)
        ->where('home_team_id', $team2->id)
        ->where('away_team_id', $team1->id)
        ->first();

    expect($homeMatch)->not->toBeNull()
        ->and($awayMatch)->not->toBeNull();
});

test('it generates fixtures with correct week numbers', function () {
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
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    $action = app(GenerateFixturesAction::class);
    $action->execute($season);

    // For 4 teams: weeks 1-3 (first half) and weeks 4-6 (second half)
    $weekNumbers = Fixture::query()
        ->where('season_id', $season->id)
        ->pluck('week_number')
        ->unique()
        ->sort()
        ->values();

    expect($weekNumbers->toArray())->toBe([1, 2, 3, 4, 5, 6]);
});

test('it creates fixtures with null scores initially', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $action = app(GenerateFixturesAction::class);
    $action->execute($season);

    $fixtures = Fixture::query()->where('season_id', $season->id)->get();

    expect($fixtures->every(fn($fixture) => $fixture->home_score === null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->away_score === null))->toBeTrue()
        ->and($fixtures->every(fn($fixture) => $fixture->played_at === null))->toBeTrue();
});
