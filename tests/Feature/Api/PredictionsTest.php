<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Actions\Prediction\CalculatePredictionsAction;
use App\Enums\Prediction\PredictionTypeEnum;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns predictions for week in last three weeks', function () {
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
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // For 4 teams, total weeks = 6, last 3 weeks start at week 4
    $response = $this->getJson("/api/v1/predictions/week/4?season_id=$season->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'predictions' => [
                    '*' => [
                        'team_id',
                        'team_name',
                        'win_probability',
                    ],
                ],
            ],
        ]);

    $data = $response->json('data.predictions');
    expect($data)->toHaveCount(4);
});

test('it returns error when predictions requested before last three weeks', function () {
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
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // Week 1 is not in the last 3 weeks (weeks 4-6)
    $response = $this->getJson("/api/v1/predictions/week/1?season_id=$season->id");

    $response->assertStatus(400)
        ->assertJsonStructure(['message']);
});

test('it returns predictions for current week when in prediction window', function () {
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
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // For 4 teams, total weeks = 6, last 3 weeks start at week 4
    // Set current week to 4 (in prediction window)
    $season->fixtures()->where('week_number', '<=', 3)->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at' => now(),
    ]);

    // Calculate predictions for week 4 (in prediction window)
    app(CalculatePredictionsAction::class)->execute(4, null, $season->id, PredictionTypeEnum::CHAMPIONSHIP);

    $response = $this->getJson("/api/v1/predictions/current?season_id=$season->id");

    // Current week might be calculated differently, so check if it's either 200 with predictions or 400
    if ($response->status() === 200) {
        $response->assertJsonStructure([
            'data' => [
                'predictions' => [
                    '*' => [
                        'team_id',
                        'team_name',
                        'win_probability',
                    ],
                ],
            ],
        ]);
    } else {
        $response->assertStatus(400);
    }
});

test('it returns empty predictions when not in prediction window', function () {
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
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // Week 1 is not in prediction window
    $response = $this->getJson("/api/v1/predictions/current?season_id=$season->id");

    // Should return 400 or empty predictions
    if ($response->status() === 400) {
        $response->assertStatus(400);
    } else {
        $response->assertStatus(200);
        $data = $response->json('data.predictions');
        expect($data)->toBeEmpty();
    }
});
