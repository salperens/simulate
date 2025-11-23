<?php

use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it correctly identifies played fixture', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    expect($fixture->isPlayed())->toBeTrue();
});

test('it correctly identifies unplayed fixture', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    expect($fixture->isPlayed())->toBeFalse();
});

test('it returns home team id as winner when home wins', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    expect($fixture->getWinnerId())->toBe($team1->id);
});

test('it returns away team id as winner when away wins', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 1,
        'away_score'   => 3,
        'played_at'    => now(),
    ]);

    expect($fixture->getWinnerId())->toBe($team2->id);
});

test('it returns null as winner when match is a draw', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 2,
        'played_at'    => now(),
    ]);

    expect($fixture->getWinnerId())->toBeNull();
});

test('it returns null as winner when fixture is not played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    expect($fixture->getWinnerId())->toBeNull();
});

test('it returns home team id as loser when home loses', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 1,
        'away_score'   => 3,
        'played_at'    => now(),
    ]);

    expect($fixture->getLoserId())->toBe($team1->id);
});

test('it returns away team id as loser when away loses', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    expect($fixture->getLoserId())->toBe($team2->id);
});

test('it returns null as loser when match is a draw', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 2,
        'away_score'   => 2,
        'played_at'    => now(),
    ]);

    expect($fixture->getLoserId())->toBeNull();
});

test('it returns null as loser when fixture is not played', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create();
    /** @var Team $team2 */
    $team2 = Team::factory()->create();

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    /** @var Fixture $fixture */
    $fixture = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => null,
        'away_score'   => null,
        'played_at'    => null,
    ]);

    expect($fixture->getLoserId())->toBeNull();
});

