<?php

namespace App\Http\Resources\Api\V1;

use App\Prediction\PredictionResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PredictionResult $resource
 */
class PredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'week'             => $this->resource->week,
            'type'             => $this->resource->type->value,
            'simulations_run'  => $this->resource->simulationsRun,
            'early_terminated' => $this->resource->earlyTerminated,
            'predictions'      => TeamPredictionResource::collection($this->resource->predictions),
        ];
    }
}
