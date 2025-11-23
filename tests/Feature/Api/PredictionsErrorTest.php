<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns 404 when season not found for predictions by week', function () {
    $response = $this->getJson('/api/v1/predictions/week/4?season_id=999');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when current season does not exist for predictions', function () {
    $response = $this->getJson('/api/v1/predictions/current');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

test('it returns 404 when season not found for predictions current', function () {
    $response = $this->getJson('/api/v1/predictions/current?season_id=999');

    $response->assertStatus(404)
        ->assertJsonStructure(['message']);
});

