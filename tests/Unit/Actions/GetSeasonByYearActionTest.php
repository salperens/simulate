<?php

use App\Actions\League\GetSeasonByYearAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns season by year', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByYearAction::class);
    $result = $action->execute(2025);

    expect($result->id)->toBe($season->id)
        ->and($result->year)->toBe(2025);
});

test('it throws exception when season for year does not exist', function () {
    $action = app(GetSeasonByYearAction::class);

    expect(fn() => $action->execute(2099))
        ->toThrow(SeasonNotFoundException::class);
});

test('it returns correct season when multiple seasons exist', function () {
    /** @var Season $season2024 */
    $season2024 = Season::factory()->create([
        'year'   => 2024,
        'status' => SeasonStatusEnum::COMPLETED,
    ]);

    Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonByYearAction::class);
    $result = $action->execute(2024);

    expect($result->id)->toBe($season2024->id)
        ->and($result->year)->toBe(2024);
});

