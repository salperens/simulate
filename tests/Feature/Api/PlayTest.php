<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it plays all fixtures for a specific week', function () {
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

    $response = $this->postJson("/api/v1/league/week/1/play?season_id=$season->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'week',
                'matches_played',
            ],
        ])
        ->assertJson([
            'data' => [
                'week' => 1,
            ],
        ]);

    $matchesPlayed = $response->json('data.matches_played');
    expect($matchesPlayed)->toBeGreaterThan(0);

    // Verify fixtures are played
    $week1Fixtures = $season->fixtures()->where('week_number', 1)->get();
    expect($week1Fixtures->every(fn($f) => $f->played_at !== null))->toBeTrue();
});

test('it only plays fixtures for the specified week', function () {
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

    $this->postJson("/api/v1/league/week/1/play?season_id=$season->id");

    // Week 2 fixtures should not be played
    $week2Fixtures = $season->fixtures()->where('week_number', 2)->get();
    expect($week2Fixtures->every(fn($f) => $f->played_at === null))->toBeTrue();
});

test('it plays all remaining fixtures', function () {
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

    $totalFixtures = $season->fixtures()->count();

    $response = $this->postJson("/api/v1/league/play-all?season_id=$season->id");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'matches_played',
            ],
        ]);

    $matchesPlayed = $response->json('data.matches_played');
    expect($matchesPlayed)->toBe($totalFixtures);

    // All fixtures should be played
    $unplayedFixtures = $season->fixtures()->whereNull('played_at')->count();
    expect($unplayedFixtures)->toBe(0);
});

test('it returns zero when all fixtures are already played', function () {
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

    // Play all fixtures first
    $season->fixtures()->update([
        'home_score' => 2,
        'away_score' => 1,
        'played_at' => now(),
    ]);

    $response = $this->postJson("/api/v1/league/play-all?season_id=$season->id");

    $response->assertStatus(200);
    $matchesPlayed = $response->json('data.matches_played');
    expect($matchesPlayed)->toBe(0);
});

test('it returns validation error when week is invalid', function () {
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

    $totalWeeks = $season->getTotalWeeks();
    $invalidWeek = $totalWeeks + 1;

    $response = $this->postJson("/api/v1/league/week/$invalidWeek/play?season_id=$season->id");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['week']);
});

test('it recalculates standings after playing week', function () {
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

    $this->postJson("/api/v1/league/week/1/play?season_id=$season->id");

    $standingsResponse = $this->getJson("/api/v1/standings?season_id=$season->id");
    $standingsData = $standingsResponse->json('data');

    // At least one team should have played matches
    $hasPlayedMatches = collect($standingsData)->some(fn($standing) => $standing['played'] > 0);
    expect($hasPlayedMatches)->toBeTrue();
});
