<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns 404 when season not found for play week', function () {
    $response = $this->postJson('/api/v1/league/week/1/play?season_id=999');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when season not found for play all', function () {
    $response = $this->postJson('/api/v1/league/play-all?season_id=999');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});


