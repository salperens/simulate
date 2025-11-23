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
        'played_at'  => now(),
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
        'played_at'  => now(),
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

test('it returns standings up to a specific week', function () {
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

    // Play week 1: Team 1 wins 2-1
    $week1Fixture = $season->fixtures()->where('week_number', 1)->first();
    $week1Fixture->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at'  => now(),
    ]);

    // Play week 2: Check which team is home/away and set scores accordingly
    $week2Fixture = $season->fixtures()->where('week_number', 2)->first();
    // Week 2 is the reverse fixture, so if Team 1 was home in week 1, Team 2 is home in week 2
    if ($week2Fixture->home_team_id === $team2->id) {
        // Team 2 is home, Team 2 wins 3-0
        $week2Fixture->update([
            'home_score' => 3,
            'away_score' => 0,
            'played_at'  => now(),
        ]);
    } else {
        // Team 1 is home, Team 2 wins 0-3 (away)
        $week2Fixture->update([
            'home_score' => 0,
            'away_score' => 3,
            'played_at'  => now(),
        ]);
    }

    // Get standings up to week 1
    $response = $this->getJson("/api/v1/standings?season_id=$season->id&week=1");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');

    $team1Standing = collect($response->json('data'))->firstWhere('id', $team1->id);
    $team2Standing = collect($response->json('data'))->firstWhere('id', $team2->id);

    // Team 1 should have 3 points (won week 1)
    expect($team1Standing['points'])->toBe(3)
        ->and($team1Standing['played'])->toBe(1)
        ->and($team2Standing['points'])->toBe(0)
        ->and($team2Standing['played'])->toBe(1);

    // Get standings up to week 2
    $response = $this->getJson("/api/v1/standings?season_id=$season->id&week=2");

    $response->assertStatus(200);

    $team1Standing = collect($response->json('data'))->firstWhere('id', $team1->id);
    $team2Standing = collect($response->json('data'))->firstWhere('id', $team2->id);

    // Team 2 should have 3 points (won week 2), Team 1 still has 3 points
    expect($team2Standing['points'])->toBe(3)
        ->and($team2Standing['played'])->toBe(2)
        ->and($team1Standing['points'])->toBe(3)
        ->and($team1Standing['played'])->toBe(2);
});
