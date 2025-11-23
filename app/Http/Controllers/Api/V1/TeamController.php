<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeamResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TeamController extends Controller
{
    /**
     * Get all teams.
     */
    public function index(): AnonymousResourceCollection
    {
        $teams = Team::orderBy('name')->get();

        return TeamResource::collection($teams);
    }
}
