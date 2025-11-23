<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fixture\GetFixturesByWeekAction;
use App\Actions\Fixture\UpdateFixtureAction;
use App\Actions\League\GetSeasonByYearAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateFixtureRequest;
use App\Http\Resources\Api\V1\FixtureResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FixtureController extends Controller
{
    public function __construct(
        private readonly GetSeasonByYearAction   $getSeasonByYearAction,
        private readonly GetFixturesByWeekAction $getFixturesByWeekAction,
        private readonly UpdateFixtureAction     $updateFixtureAction,
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

    /**
     * Update fixture result.
     */
    public function update(int $id, UpdateFixtureRequest $request): FixtureResource
    {
        $data = $request->toData();
        $fixture = $this->updateFixtureAction->execute(
            $id,
            $data->homeScore,
            $data->awayScore
        );

        return new FixtureResource($fixture);
    }
}
