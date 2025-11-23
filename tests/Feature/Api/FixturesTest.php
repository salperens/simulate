<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns fixtures for a specific week', function () {
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

    $response = $this->getJson('/api/v1/fixtures/week/1');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'week_number',
                    'home_team',
                    'away_team',
                    'home_score',
                    'away_score',
                    'played_at',
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['week_number'])->toBe(1);
});

test('it returns fixtures for specific season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season2024 */
    $season2024 = Season::factory()->create(['year' => 2024]);
    $season2024->teams()->attach([$team1->id, $team2->id]);

    /** @var Season $season2025 */
    $season2025 = Season::factory()->create(['year' => 2025]);
    $season2025->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season2024);
    app(GenerateFixturesAction::class)->execute($season2025);

    $response = $this->getJson("/api/v1/fixtures/week/1?season_id=$season2024->id");

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
});

test('it returns empty array when no fixtures exist for week', function () {
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

    $response = $this->getJson('/api/v1/fixtures/week/999');

    $response->assertStatus(200)
        ->assertJson(['data' => []]);
});

test('it updates fixture result', function () {
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

    /** @var Fixture $fixture */
    $fixture = $season->fixtures()->first();

    $fixture->update([
        'home_score' => 1,
        'away_score' => 0,
        'played_at' => now(),
    ]);

    $response = $this->putJson("/api/v1/fixtures/$fixture->id", [
        'home_score' => 3,
        'away_score' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'home_score',
                'away_score',
                'played_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'home_score' => 3,
                'away_score' => 1,
            ],
        ]);

    $fixture->refresh();
    expect($fixture->home_score)->toBe(3)
        ->and($fixture->away_score)->toBe(1)
        ->and($fixture->played_at)->not->toBeNull();
});

test('it recalculates standings after updating fixture', function () {
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

    /** @var Fixture $fixture */
    $fixture = $season->fixtures()->first();

    $fixture->update([
        'home_score' => 1,
        'away_score' => 0,
        'played_at' => now(),
    ]);

    $this->putJson("/api/v1/fixtures/$fixture->id", [
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $standingsResponse = $this->getJson('/api/v1/standings');
    $standingsData = $standingsResponse->json('data');

    $team1Standing = collect($standingsData)->firstWhere('id', $team1->id);
    expect($team1Standing['points'])->toBe(3)
        ->and($team1Standing['won'])->toBe(1);
});

test('it returns validation error when updating fixture with invalid scores', function () {
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

    /** @var Fixture $fixture */
    $fixture = $season->fixtures()->first();

    $response = $this->putJson("/api/v1/fixtures/$fixture->id", [
        'home_score' => -1,
        'away_score' => 1,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['home_score']);
});

test('it returns 404 when fixture not found', function () {
    $response = $this->putJson('/api/v1/fixtures/999', [
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $response->assertStatus(404);
});
