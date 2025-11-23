<?php

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use App\Rules\WeekInSeasonRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

test('it passes validation for valid week number', function () {
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

    $validator = Validator::make(
        ['week' => 1],
        ['week' => [new WeekInSeasonRange()]]
    );

    expect($validator->passes())->toBeTrue();
});

test('it fails validation when week is less than 1', function () {
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

    $validator = Validator::make(
        ['week' => 0],
        ['week' => [new WeekInSeasonRange()]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('week'))->toContain('at least 1');
});

test('it fails validation when week exceeds total weeks', function () {
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

    $validator = Validator::make(
        ['week' => $totalWeeks + 1],
        ['week' => [new WeekInSeasonRange()]]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('week'))->toContain('not be greater than');
});

test('it passes validation for last week', function () {
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

    $validator = Validator::make(
        ['week' => $totalWeeks],
        ['week' => [new WeekInSeasonRange()]]
    );

    expect($validator->passes())->toBeTrue();
});

test('it throws exception when current season does not exist', function () {
    $validator = Validator::make(
        ['week' => 1],
        ['week' => [new WeekInSeasonRange()]]
    );

    expect(fn() => $validator->validate())
        ->toThrow(\App\Exceptions\League\SeasonNotFoundException::class);
});
