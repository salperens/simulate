<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns all seasons ordered by year descending', function () {
    Season::factory()->create(['year' => 2024]);
    Season::factory()->create(['year' => 2025]);
    Season::factory()->create(['year' => 2023]);

    $response = $this->getJson('/api/v1/seasons');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'year',
                    'name',
                    'status',
                    'current_week',
                    'total_weeks',
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data[0]['year'])->toBe(2025)
        ->and($data[1]['year'])->toBe(2024)
        ->and($data[2]['year'])->toBe(2023);
});

test('it returns specific season by id', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    app(GenerateFixturesAction::class)->execute($season);

    $response = $this->getJson("/api/v1/seasons/$season->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'year',
                'name',
                'status',
                'current_week',
                'total_weeks',
            ],
        ])
        ->assertJson([
            'data' => [
                'id'     => $season->id,
                'year'   => 2025,
                'status' => 'active',
            ],
        ]);
});

test('it creates a new season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $response = $this->postJson('/api/v1/seasons', [
        'year'     => 2026,
        'name'     => '2026 Season',
        'team_ids' => [$team1->id, $team2->id],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'year',
                'name',
                'status',
            ],
        ])
        ->assertJson([
            'data' => [
                'year'   => 2026,
                'name'   => '2026 Season',
                'status' => 'draft',
            ],
        ]);

    $this->assertDatabaseHas('seasons', [
        'year'   => 2026,
        'name'   => '2026 Season',
        'status' => SeasonStatusEnum::DRAFT->value,
    ]);

    $seasonId = $response->json('data.id');
    $season = Season::query()->find($seasonId);
    expect($season->teams->pluck('id')->toArray())->toContain($team1->id, $team2->id);
});

test('it generates default name when name is not provided', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    $response = $this->postJson('/api/v1/seasons', [
        'year'     => 2027,
        'team_ids' => [$team1->id, $team2->id],
    ]);

    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data['name'])->toBe('2027-2028 Season');
});

test('it starts a draft season', function () {
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

    app(GenerateFixturesAction::class)->execute($season);

    $response = $this->postJson("/api/v1/seasons/$season->id/start");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => 'active',
            ],
        ]);

    $this->assertDatabaseHas('seasons', [
        'id'     => $season->id,
        'status' => SeasonStatusEnum::ACTIVE->value,
    ]);
});

test('it completes an active season when all matches are played', function () {
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

    // Play all fixtures
    $season->fixtures()->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at'  => now(),
    ]);

    $response = $this->postJson("/api/v1/seasons/$season->id/complete");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'status' => 'completed',
            ],
        ]);

    $this->assertDatabaseHas('seasons', [
        'id'     => $season->id,
        'status' => SeasonStatusEnum::COMPLETED->value,
    ]);
});

test('it returns current season', function () {
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

    $response = $this->getJson('/api/v1/season/current');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id'     => $season->id,
                'year'   => now()->year,
                'status' => 'active',
            ],
        ]);
});

test('it returns 404 when season not found', function () {
    $response = $this->getJson('/api/v1/seasons/999');

    $response->assertStatus(404);
});

test('it returns validation error when creating season without teams', function () {
    $response = $this->postJson('/api/v1/seasons', [
        'year'     => 2026,
        'team_ids' => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_ids']);
});

test('it returns error when trying to create season with active season exists', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => now()->year,
    ]);

    $response = $this->postJson('/api/v1/seasons', [
        'year'     => now()->year + 1,
        'team_ids' => [$team1->id, $team2->id],
    ]);

    $response->assertStatus(409);
});
