<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Team $resource
 */
class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->resource->id,
            'name'                    => $this->resource->name,
            'power_rating'           => $this->resource->power_rating,
            'goalkeeper_factor'      => $this->resource->goalkeeper_factor,
            'supporter_strength'     => $this->resource->supporter_strength,
            'home_advantage_multiplier' => $this->resource->home_advantage_multiplier,
        ];
    }
}

