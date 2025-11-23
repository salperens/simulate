<?php

namespace Database\Factories;

use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2030);
        $startDate = now()->setYear($year)->startOfYear();
        $endDate = now()->setYear($year)->endOfYear();

        return [
            'year' => $year,
            'name' => "{$year} Season",
            'status' => SeasonStatusEnum::DRAFT,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}

