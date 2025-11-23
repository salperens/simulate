<?php

namespace App\Http\Resources\Api\V1;

use App\Data\Prediction\TeamPredictionData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TeamPredictionData $resource
 */
class TeamPredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'team_id'         => $this->resource->teamId,
            'team_name'       => $this->resource->teamName,
            'win_probability' => $this->resource->winProbability,
        ];
    }
}
