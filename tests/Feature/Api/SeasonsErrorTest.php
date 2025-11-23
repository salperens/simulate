<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns error when trying to start non-draft season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::COMPLETED, // Use completed instead of active to avoid 409
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $response = $this->postJson("/api/v1/seasons/$season->id/start");

    $response->assertStatus(400)
        ->assertJsonStructure(['message']);
});

test('it returns error when trying to start completed season', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::COMPLETED,
        'year'   => 2025,
    ]);
    $season->teams()->attach([$team1->id, $team2->id]);

    $response = $this->postJson("/api/v1/seasons/$season->id/start");

    $response->assertStatus(400)
        ->assertJsonStructure(['message']);
});

test('it returns error when trying to complete non-active season', function () {
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

    $response = $this->postJson("/api/v1/seasons/$season->id/complete");

    $response->assertStatus(400)
        ->assertJsonStructure(['message']);
});

test('it returns error when trying to complete season with unplayed matches', function () {
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

    // Don't play all fixtures
    $response = $this->postJson("/api/v1/seasons/$season->id/complete");

    $response->assertStatus(400)
        ->assertJsonStructure(['message']);
});

test('it returns error when season year already exists', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    Season::factory()->create(['year' => 2026]);

    $response = $this->postJson('/api/v1/seasons', [
        'year'     => 2026,
        'team_ids' => [$team1->id, $team2->id],
    ]);

    $response->assertStatus(409)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when current season does not exist', function () {
    $response = $this->getJson('/api/v1/season/current');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when trying to start non-existent season', function () {
    $response = $this->postJson('/api/v1/seasons/999/start');

    $response->assertStatus(404);
});

test('it returns 404 when trying to complete non-existent season', function () {
    $response = $this->postJson('/api/v1/seasons/999/complete');

    $response->assertStatus(404);
});

