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
        'year'   => 2025,
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
        'year'   => 2025,
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
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    // Generate fixtures
    app(GenerateFixturesAction::class)->execute($season);

    $action = app(PlayWeekAction::class);
    $result = $action->execute($season, 1);

    // For 2 teams, there should be 1 match per week
    expect($result->matchesPlayed)->toBe(1);
});

test('it calculates predictions when playing a week in prediction window', function () {
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

    // Generate fixtures (for 4 teams, total weeks = 6)
    app(GenerateFixturesAction::class)->execute($season);

    $totalWeeks = $season->getTotalWeeks();
    $lastThreeWeeksStart = max(1, $totalWeeks - 2); // Week 4 for 6-week season

    // Play weeks before prediction window (should not calculate predictions)
    for ($week = 1; $week < $lastThreeWeeksStart; $week++) {
        $action = app(PlayWeekAction::class);
        $action->execute($season, $week);
    }

    // No predictions should exist yet
    $predictionsBefore = \App\Models\ChampionshipPrediction::query()
        ->where('season_id', $season->id)
        ->count();
    expect($predictionsBefore)->toBe(0);

    // Play a week in prediction window (should calculate predictions)
    $action = app(PlayWeekAction::class);
    $action->execute($season, $lastThreeWeeksStart);

    // Predictions should now exist for this week
    $predictions = \App\Models\ChampionshipPrediction::query()
        ->where('season_id', $season->id)
        ->where('week_number', $lastThreeWeeksStart)
        ->get();

    expect($predictions)->not->toBeEmpty()
        ->and($predictions->count())->toBe(4); // One prediction per team
});
