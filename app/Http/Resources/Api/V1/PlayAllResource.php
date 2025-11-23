<?php

namespace App\Http\Resources\Api\V1;

use App\Data\League\PlayAllResponseData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PlayAllResponseData $resource
 */
class PlayAllResource extends JsonResource
{
    private const RESPONSE_MESSAGE = 'All matches played successfully';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => self::RESPONSE_MESSAGE,
            'data'    => [
                'matches_played' => $this->resource->matchesPlayed,
            ],
        ];
    }
}

