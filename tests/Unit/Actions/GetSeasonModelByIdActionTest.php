<?php

use App\Actions\Season\GetSeasonModelByIdAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Exceptions\League\SeasonNotFoundException;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns season model by id', function () {
    /** @var Season $season */
    $season = Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonModelByIdAction::class);
    $result = $action->execute($season->id);

    expect($result)->toBeInstanceOf(Season::class)
        ->and($result->id)->toBe($season->id)
        ->and($result->year)->toBe(2025)
        ->and($result->status)->toBe(SeasonStatusEnum::ACTIVE);
});

test('it throws exception when season id does not exist', function () {
    $action = app(GetSeasonModelByIdAction::class);

    expect(fn() => $action->execute(99999))
        ->toThrow(SeasonNotFoundException::class);
});

test('it returns correct season when multiple seasons exist', function () {
    /** @var Season $season1 */
    $season1 = Season::factory()->create([
        'year'   => 2024,
        'status' => SeasonStatusEnum::COMPLETED,
    ]);

    Season::factory()->create([
        'year'   => 2025,
        'status' => SeasonStatusEnum::ACTIVE,
    ]);

    $action = app(GetSeasonModelByIdAction::class);
    $result = $action->execute($season1->id);

    expect($result->id)->toBe($season1->id)
        ->and($result->year)->toBe(2024);
});

