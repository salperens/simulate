<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\League\GetSeasonByYearAction;
use App\Actions\League\PlayAllWeeksAction;
use App\Actions\League\PlayWeekAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlayWeekRequest;
use App\Http\Resources\Api\V1\PlayAllResource;
use App\Http\Resources\Api\V1\PlayWeekResource;

final class PlayController extends Controller
{
    public function __construct(
        private readonly GetSeasonByYearAction $getSeasonByYearAction,
        private readonly PlayWeekAction        $playWeekAction,
        private readonly PlayAllWeeksAction    $playAllWeeksAction,
    )
    {
    }

    /**
     * Play a specific week.
     */
    public function week(PlayWeekRequest $request, int $week): PlayWeekResource
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $response = $this->playWeekAction->execute($season, $week);

        return new PlayWeekResource($response);
    }

    /**
     * Play all remaining fixtures.
     */
    public function all(): PlayAllResource
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $response = $this->playAllWeeksAction->execute($season);

        return new PlayAllResource($response);
    }
}
