<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates total weeks correctly', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    // For 4 teams: 3 weeks first half + 3 weeks second half = 6 weeks total
    expect($season->getTotalWeeks())->toBe(6);
});

test('it returns zero when no fixtures exist', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);

    expect($season->getTotalWeeks())->toBe(0);
});

test('it returns fixtures for a specific week', function () {
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

    $week1Fixtures = $season->fixturesForWeek(1);

    expect($week1Fixtures)->not->toBeEmpty()
        ->and($week1Fixtures->every(fn($fixture) => $fixture->week_number === 1))->toBeTrue();
});

test('it returns empty collection when no fixtures exist for week', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::DRAFT,
        'year'   => 2025,
    ]);

    $fixtures = $season->fixturesForWeek(1);

    expect($fixtures)->toBeEmpty();
});

test('it checks if season has fixtures', function () {
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

    expect($season->hasFixtures())->toBeFalse();

    app(GenerateFixturesAction::class)->execute($season);

    expect($season->hasFixtures())->toBeTrue();
});

test('it calculates total weeks with odd number of teams', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    // For 3 teams (normalized to 4): 3 weeks first half + 3 weeks second half = 6 weeks
    expect($season->getTotalWeeks())->toBe(6);
});
