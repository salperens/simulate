<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fixture\GetFixturesByWeekAction;
use App\Actions\League\CalculateStandingsAction;
use App\Actions\League\GetSeasonByYearAction;
use App\Actions\Season\GetCurrentSeasonAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FixtureResource;
use App\Http\Resources\Api\V1\SeasonResource;
use App\Http\Resources\Api\V1\TeamStandingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueController extends Controller
{
    public function __construct(
        private readonly GetSeasonByYearAction    $getSeasonByYearAction,
        private readonly GetCurrentSeasonAction   $getCurrentSeasonAction,
        private readonly CalculateStandingsAction $calculateStandingsAction,
        private readonly GetFixturesByWeekAction  $getFixturesByWeekAction,
    )
    {
    }

    /**
     * Get league standings for the current season.
     */
    public function standings(): AnonymousResourceCollection
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $standings = $this->calculateStandingsAction->execute($season);

        return TeamStandingResource::collection($standings->teams);
    }

    /**
     * Get current season information.
     */
    public function currentSeason(): SeasonResource
    {
        $seasonData = $this->getCurrentSeasonAction->execute();

        return new SeasonResource($seasonData);
    }

    /**
     * Get fixtures for a specific week.
     */
    public function fixtures(int $week): AnonymousResourceCollection
    {
        $season = $this->getSeasonByYearAction->execute(now()->year);
        $fixtures = $this->getFixturesByWeekAction->execute($season, $week);

        return FixtureResource::collection($fixtures);
    }
}
