<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Prediction\CalculatePredictionsAction;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\Prediction\PredictionNotAvailableException;
use App\Models\ChampionshipPrediction;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it calculates predictions for the last three weeks', function () {
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

    // Generate fixtures (for 4 teams, total weeks = 6: 3 first half + 3 second half)
    app(GenerateFixturesAction::class)->execute($season);

    // For 4 teams, total weeks = 6, last 3 weeks start at week 4
    // Use week 4 which is in the prediction window

    $action = app(CalculatePredictionsAction::class);
    $result = $action->execute(4, null, $season->id);

    expect($result->predictions)->not->toBeEmpty();
});

test('it throws exception when predictions requested before last three weeks', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();
    /** @var Team $team3 */
    $team3 = Team::factory()->create();
    /** @var Team $team4 */
    $team4 = Team::factory()->create();
    /** @var Team $team5 */
    $team5 = Team::factory()->create();
    /** @var Team $team6 */
    $team6 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id, $team5->id, $team6->id]);

    // Generate fixtures (for 6 teams, total weeks = 5)
    app(GenerateFixturesAction::class)->execute($season);

    // For 6 teams, total weeks = 5, last 3 weeks start at week 3
    // Week 1 is before the prediction window

    $action = app(CalculatePredictionsAction::class);

    expect(fn() => $action->execute(1, null, $season->id))
        ->toThrow(PredictionNotAvailableException::class);
});

test('it saves predictions to database', function () {
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

    $action = app(CalculatePredictionsAction::class);
    $action->execute(4, null, $season->id); // Week 4 is in last 3 weeks

    $predictions = ChampionshipPrediction::query()->where('season_id', $season->id)->get();

    expect($predictions)->not->toBeEmpty()
        ->and($predictions->count())->toBe(4); // One prediction per team
});

test('it calculates predictions with correct type', function () {
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

    $action = app(CalculatePredictionsAction::class);
    $result = $action->execute(4, null, $season->id, PredictionTypeEnum::CHAMPIONSHIP); // Week 4 is in last 3 weeks

    expect($result->type)->toBe(PredictionTypeEnum::CHAMPIONSHIP);
});

test('it allows predictions in the last week', function () {
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

    $action = app(CalculatePredictionsAction::class);
    $result = $action->execute(6, null, $season->id); // Last week (week 6)

    expect($result->predictions)->not->toBeEmpty();
});

test('it includes all teams in predictions', function () {
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

    $action = app(CalculatePredictionsAction::class);
    $result = $action->execute(4, null, $season->id); // Week 4 is in last 3 weeks

    $teamIds = $result->predictions->pluck('teamId')->toArray();
    expect($teamIds)->toContain($team1->id, $team2->id, $team3->id, $team4->id)
        ->and(count($teamIds))->toBe(4);
});

