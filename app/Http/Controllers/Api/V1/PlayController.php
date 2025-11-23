<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\League\PlayAllWeeksAction;
use App\Actions\League\PlayWeekAction;
use App\Actions\Season\GetSeasonByIdOrCurrentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlayWeekRequest;
use App\Http\Resources\Api\V1\PlayAllResource;
use App\Http\Resources\Api\V1\PlayWeekResource;
use Illuminate\Http\Request;

final class PlayController extends Controller
{
    public function __construct(
        private readonly GetSeasonByIdOrCurrentAction $getSeasonByIdOrCurrentAction,
        private readonly PlayWeekAction               $playWeekAction,
        private readonly PlayAllWeeksAction           $playAllWeeksAction,
    )
    {
    }

    /**
     * Play a specific week.
     * If season_id is provided, use that season. Otherwise, use current season.
     */
    public function week(PlayWeekRequest $request, int $week): PlayWeekResource
    {
        $seasonId = $request->query('season_id');
        $season = $this->getSeasonByIdOrCurrentAction->execute($seasonId !== null ? (int)$seasonId : null);
        $response = $this->playWeekAction->execute($season, $week);

        return new PlayWeekResource($response);
    }

    /**
     * Play all remaining fixtures.
     * If season_id is provided, use that season. Otherwise, use current season.
     */
    public function all(Request $request): PlayAllResource
    {
        $seasonId = $request->query('season_id');
        $season = $this->getSeasonByIdOrCurrentAction->execute($seasonId !== null ? (int)$seasonId : null);
        $response = $this->playAllWeeksAction->execute($season);

        return new PlayAllResource($response);
    }
}
