<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\League\CalculateStandingsAction;
use App\Actions\Season\GetSeasonByIdOrCurrentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamStandingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StandingsController extends Controller
{
    public function __construct(
        private readonly GetSeasonByIdOrCurrentAction $getSeasonByIdOrCurrentAction,
        private readonly CalculateStandingsAction    $calculateStandingsAction,
    )
    {
    }

    /**
     * Get league standings for a season.
     * If season_id is provided, use that season. Otherwise, use current season.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $seasonId = $request->query('season_id');
        $season = $this->getSeasonByIdOrCurrentAction->execute($seasonId !== null ? (int)$seasonId : null);
        $standings = $this->calculateStandingsAction->execute($season);

        return TeamStandingResource::collection($standings);
    }
}
