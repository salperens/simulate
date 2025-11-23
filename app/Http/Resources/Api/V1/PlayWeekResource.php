<?php

namespace App\Http\Resources\Api\V1;

use App\Data\League\PlayWeekResponseData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PlayWeekResponseData $resource
 */
class PlayWeekResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message'        => "Week {$this->resource->week} played successfully",
            'data'           => [
                'week'            => $this->resource->week,
                'matches_played'  => $this->resource->matchesPlayed,
            ],
        ];
    }
}
