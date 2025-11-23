<?php

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns all teams', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    $response = $this->getJson('/api/v1/teams');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'power_rating',
                    'goalkeeper_factor',
                    'supporter_strength',
                    'home_advantage_multiplier',
                ],
            ],
        ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(2)
        ->and(collect($data)->pluck('id')->toArray())->toContain($team1->id, $team2->id);
});

test('it returns empty array when no teams exist', function () {
    $response = $this->getJson('/api/v1/teams');

    $response->assertStatus(200)
        ->assertJson(['data' => []]);
});
