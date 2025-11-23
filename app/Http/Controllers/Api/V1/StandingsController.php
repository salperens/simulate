<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\League\CalculateStandingsAction;
use App\Actions\League\GetSeasonByYearAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamStandingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StandingsController extends Controller
{
    public function __construct(
        private readonly GetSeasonByYearAction    $getSeasonByYearAction,
        private readonly CalculateStandingsAction $calculateStandingsAction,
    )
    {
    }

    /**
     * Get league standings for the current season.
     */
    public function index(): AnonymousResourceCollection
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $standings = $this->calculateStandingsAction->execute($season);

        return TeamStandingResource::collection($standings);
    }
}
