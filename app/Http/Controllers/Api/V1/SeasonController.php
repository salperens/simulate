<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Season\GetCurrentSeasonAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SeasonResource;

final class SeasonController extends Controller
{
    public function __construct(
        private readonly GetCurrentSeasonAction $getCurrentSeasonAction,
    )
    {
    }

    /**
     * Get current season information.
     */
    public function current(): SeasonResource
    {
        $seasonData = $this->getCurrentSeasonAction->execute();

        return new SeasonResource($seasonData);
    }
}
