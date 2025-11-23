<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns standings for current season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season);

    // Play a match: Team 1 wins 2-1
    $fixture = $season->fixtures()->first();
    $fixture->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/standings');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'played',
                    'won',
                    'drawn',
                    'lost',
                    'goals_for',
                    'goals_against',
                    'goal_difference',
                    'points',
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(2);

    $team1Standing = collect($data)->firstWhere('id', $team1->id);
    expect($team1Standing['points'])->toBe(3)
        ->and($team1Standing['won'])->toBe(1);
});

test('it returns standings for specific season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season2024 */
    $season2024 = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2024,
    ]);
    $season2024->teams()->attach([$team1->id, $team2->id]);

    /** @var Season $season2025 */
    $season2025 = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);
    $season2025->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season2024);
    app(GenerateFixturesAction::class)->execute($season2025);

    // Play match in 2024 season
    $fixture = $season2024->fixtures()->first();
    $fixture->update([
        'home_score' => 3,
        'away_score' => 0,
        'played_at' => now(),
    ]);

    $response = $this->getJson("/api/v1/standings?season_id=$season2024->id");

    $response->assertStatus(200);
    $data = $response->json('data');
    $team1Standing = collect($data)->firstWhere('id', $team1->id);
    expect($team1Standing['points'])->toBe(3);
});

test('it returns empty standings when no matches played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => now()->year,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season);

    $response = $this->getJson('/api/v1/standings');

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->toHaveCount(2);

    $team1Standing = collect($data)->firstWhere('id', $team1->id);
    expect($team1Standing['points'])->toBe(0)
        ->and($team1Standing['played'])->toBe(0);
});
