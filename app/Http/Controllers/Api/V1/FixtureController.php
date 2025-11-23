<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fixture\GetFixturesByWeekAction;
use App\Actions\League\GetSeasonByYearAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FixtureResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FixtureController extends Controller
{
    public function __construct(
        private readonly GetSeasonByYearAction   $getSeasonByYearAction,
        private readonly GetFixturesByWeekAction $getFixturesByWeekAction,
    )
    {
    }

    /**
     * Get fixtures for a specific week.
     */
    public function byWeek(int $week): AnonymousResourceCollection
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $fixtures = $this->getFixturesByWeekAction->execute($season, $week);

        return FixtureResource::collection($fixtures);
    }
}
