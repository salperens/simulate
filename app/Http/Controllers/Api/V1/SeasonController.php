<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Season\CompleteSeasonAction;
use App\Actions\Season\CreateSeasonAction;
use App\Actions\Season\GetAllSeasonsAction;
use App\Actions\Season\GetCurrentSeasonAction;
use App\Actions\Season\GetSeasonByIdAction;
use App\Actions\Season\StartSeasonAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateSeasonRequest;
use App\Http\Resources\Api\V1\SeasonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SeasonController extends Controller
{
    public function __construct(
        private readonly GetCurrentSeasonAction $getCurrentSeasonAction,
        private readonly GetAllSeasonsAction    $getAllSeasonsAction,
        private readonly GetSeasonByIdAction    $getSeasonByIdAction,
        private readonly CreateSeasonAction     $createSeasonAction,
        private readonly StartSeasonAction      $startSeasonAction,
        private readonly CompleteSeasonAction   $completeSeasonAction,
    )
    {
    }

    /**
     * Get all seasons.
     */
    public function index(): AnonymousResourceCollection
    {
        $seasons = $this->getAllSeasonsAction->execute();

        return SeasonResource::collection($seasons);
    }

    /**
     * Get current season information.
     */
    public function current(): SeasonResource
    {
        $seasonData = $this->getCurrentSeasonAction->execute();

        return new SeasonResource($seasonData);
    }

    /**
     * Get season by ID.
     */
    public function show(int $id): SeasonResource
    {
        $seasonData = $this->getSeasonByIdAction->execute($id);

        return new SeasonResource($seasonData);
    }

    /**
     * Create a new season.
     */
    public function store(CreateSeasonRequest $request): SeasonResource
    {
        $data = $request->toData();
        $seasonData = $this->createSeasonAction->execute(
            $data->year,
            $data->teamIds,
            $data->name
        );

        return new SeasonResource($seasonData);
    }

    /**
     * Start a season (change status from DRAFT to ACTIVE).
     */
    public function start(int $id): SeasonResource
    {
        $seasonData = $this->startSeasonAction->execute($id);

        return new SeasonResource($seasonData);
    }

    /**
     * Complete a season (change status from ACTIVE to COMPLETED).
     */
    public function complete(int $id): SeasonResource
    {
        $seasonData = $this->completeSeasonAction->execute($id);

        return new SeasonResource($seasonData);
    }
}
