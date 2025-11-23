<?php

namespace App\Http\Resources\Api\V1;

use App\Data\Fixture\FixtureData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property FixtureData $resource
 */
class FixtureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->resource->id,
            'week_number' => $this->resource->week_number,
            'home_team'   => [
                'id'   => $this->resource->home_team->id,
                'name' => $this->resource->home_team->name,
            ],
            'away_team'   => [
                'id'   => $this->resource->away_team->id,
                'name' => $this->resource->away_team->name,
            ],
            'home_score'  => $this->resource->home_score,
            'away_score'  => $this->resource->away_score,
            'played_at'   => $this->resource->played_at?->format('c'),
        ];
    }
}
