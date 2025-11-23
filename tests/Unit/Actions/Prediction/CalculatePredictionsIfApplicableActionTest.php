<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Prediction\CalculatePredictionsIfApplicableAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\ChampionshipPrediction;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates predictions when week is in prediction window', function () {
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

    // For 4 teams, total weeks = 6, last 3 weeks start at week 4
    $action = app(CalculatePredictionsIfApplicableAction::class);
    $action->execute($season, 4);

    $predictions = ChampionshipPrediction::query()
        ->where('season_id', $season->id)
        ->where('week_number', 4)
        ->get();

    expect($predictions)->not->toBeEmpty()
        ->and($predictions->count())->toBe(4);
});

test('it does not calculate predictions when week is before prediction window', function () {
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

    // Week 1 is not in prediction window (last 3 weeks start at week 4)
    $action = app(CalculatePredictionsIfApplicableAction::class);
    $action->execute($season, 1);

    $predictions = ChampionshipPrediction::query()
        ->where('season_id', $season->id)
        ->where('week_number', 1)
        ->get();

    expect($predictions)->toBeEmpty();
});

test('it calculates predictions for last week', function () {
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

    $totalWeeks = $season->getTotalWeeks(); // Should be 6 for 4 teams

    $action = app(CalculatePredictionsIfApplicableAction::class);
    $action->execute($season, $totalWeeks);

    $predictions = ChampionshipPrediction::query()
        ->where('season_id', $season->id)
        ->where('week_number', $totalWeeks)
        ->get();

    expect($predictions)->not->toBeEmpty()
        ->and($predictions->count())->toBe(4);
});

