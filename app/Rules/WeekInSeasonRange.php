<?php

namespace App\Rules;

use App\Actions\League\GetSeasonByYearAction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

readonly class WeekInSeasonRange implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $week = (int)$value;

        if ($week < 1) {
            $fail('The :attribute must be at least 1.');
            return;
        }

        /** @var GetSeasonByYearAction $getSeasonByYearAction */
        $getSeasonByYearAction = app(GetSeasonByYearAction::class);

        $season = $getSeasonByYearAction->execute(now()->year);
        $totalWeeks = $season->getTotalWeeks();

        if ($week > $totalWeeks) {
            $fail("The :attribute must not be greater than {$totalWeeks}.");
        }
    }
}
