<?php

namespace App\Http\Resources\Api\V1;

use App\Data\Season\SeasonData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SeasonData $resource
 */
class SeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'year'         => $this->resource->year,
            'name'         => $this->resource->name,
            'status'       => $this->resource->status->value,
            'start_date'   => $this->resource->start_date?->format('Y-m-d'),
            'end_date'     => $this->resource->end_date?->format('Y-m-d'),
            'current_week' => $this->resource->current_week,
            'total_weeks'  => $this->resource->total_weeks,
        ];
    }
}
