<?php

use App\Actions\Fixture\GetFixturesByWeekAction;
use App\Data\Fixture\FixtureData;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns fixtures for a specific week', function () {
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
        'year'   => 2025,
    ]);

    $season->teams()->attach([$team1->id, $team2->id, $team3->id, $team4->id]);

    /** @var Fixture $fixture1 */
    $fixture1 = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
    ]);

    /** @var Fixture $fixture2 */
    $fixture2 = Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team3->id,
        'away_team_id' => $team4->id,
        'week_number'  => 1,
    ]);

    // Different week
    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team3->id,
        'week_number'  => 2,
    ]);

    $action = app(GetFixturesByWeekAction::class);
    $fixtures = $action->execute($season, 1);

    expect($fixtures)->toHaveCount(2)
        ->and($fixtures->pluck('id')->toArray())->toContain($fixture1->id, $fixture2->id);
});

test('it returns empty collection when no fixtures exist for week', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    $action = app(GetFixturesByWeekAction::class);
    $fixtures = $action->execute($season, 1);

    expect($fixtures)->toHaveCount(0);
});

test('it returns fixtures with team data loaded', function () {
    /** @var Team $team1 */
    $team1 = Team::factory()->create(['name' => 'Team A']);
    /** @var Team $team2 */
    $team2 = Team::factory()->create(['name' => 'Team B']);

    /** @var Season $season */
    $season = Season::factory()->create([
        'status' => SeasonStatusEnum::ACTIVE,
        'year'   => 2025,
    ]);

    $season->teams()->attach([$team1->id, $team2->id]);

    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
    ]);

    $action = app(GetFixturesByWeekAction::class);
    $fixtures = $action->execute($season, 1);

    /** @var FixtureData $foundFixture */
    $foundFixture = $fixtures->first();
    expect($foundFixture->homeTeam->id)->toBe($team1->id)
        ->and($foundFixture->homeTeam->name)->toBe('Team A')
        ->and($foundFixture->awayTeam->id)->toBe($team2->id)
        ->and($foundFixture->awayTeam->name)->toBe('Team B');
});

test('it returns fixtures with scores when played', function () {
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

    Fixture::factory()->create([
        'season_id'    => $season->id,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
        'week_number'  => 1,
        'home_score'   => 3,
        'away_score'   => 1,
        'played_at'    => now(),
    ]);

    $action = app(GetFixturesByWeekAction::class);
    $fixtures = $action->execute($season, 1);

    /** @var FixtureData $foundFixture */
    $foundFixture = $fixtures->first();
    expect($foundFixture->homeScore)->toBe(3)
        ->and($foundFixture->awayScore)->toBe(1)
        ->and($foundFixture->playedAt)->not->toBeNull();
});
