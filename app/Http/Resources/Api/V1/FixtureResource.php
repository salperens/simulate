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
            'week_number' => $this->resource->weekNumber,
            'home_team'   => [
                'id'   => $this->resource->homeTeam->id,
                'name' => $this->resource->homeTeam->name,
            ],
            'away_team'   => [
                'id'   => $this->resource->awayTeam->id,
                'name' => $this->resource->awayTeam->name,
            ],
            'home_score'  => $this->resource->homeScore,
            'away_score'  => $this->resource->awayScore,
            'played_at'   => $this->resource->playedAt?->format('c'),
        ];
    }
}
