<?php

namespace App\Actions\Season;

use App\Actions\League\GetSeasonByYearAction;
use App\Models\Season;

readonly class GetSeasonByIdOrCurrentAction
{
    public function __construct(
        private GetSeasonModelByIdAction $getSeasonModelByIdAction,
        private GetSeasonByYearAction    $getSeasonByYearAction,
    )
    {
    }

    public function execute(?int $seasonId = null, ?int $year = null): Season
    {
        if ($seasonId !== null) {
            return $this->getSeasonModelByIdAction->execute($seasonId);
        }

        return $this->getSeasonByYearAction->execute($year ?? now()->year);
    }
}
