<?php

use App\Actions\Season\GetSeasonByIdOrCurrentAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns season by id when season id is provided', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByIdOrCurrentAction::class);
    $result = $action->execute($season->id);

    expect($result->id)->toBe($season->id)
        ->and($result->year)->toBe(2025);
});

test('it returns current season by year when season id is null', function () {
    $currentYear = now()->year;
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => $currentYear,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByIdOrCurrentAction::class);
    $result = $action->execute(null);

    expect($result->id)->toBe($season->id)
        ->and($result->year)->toBe($currentYear);
});

test('it returns season by specific year when year is provided', function () {
    /** @var Season $season2024 */
    $season2024 = Season::factory()->create([
        'year'   => 2024,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByIdOrCurrentAction::class);
    $result = $action->execute(null, 2024);

    expect($result->id)->toBe($season2024->id)
        ->and($result->year)->toBe(2024);
});

test('it throws exception when season id does not exist', function () {
    $action = app(GetSeasonByIdOrCurrentAction::class);

    expect(fn() => $action->execute(99999))
        ->toThrow(SeasonNotFoundException::class);
});

test('it throws exception when season for year does not exist', function () {
    $action = app(GetSeasonByIdOrCurrentAction::class);

    expect(fn() => $action->execute(null, 2099))
        ->toThrow(SeasonNotFoundException::class);
});
