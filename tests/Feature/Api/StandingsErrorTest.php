<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns 404 when season not found for standings', function () {
    $response = $this->getJson('/api/v1/standings?season_id=999');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when current season does not exist for standings', function () {
    $response = $this->getJson('/api/v1/standings');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});
