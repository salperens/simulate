<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' FC',
            'power_rating' => $this->faker->numberBetween(30, 100),
            'goalkeeper_factor' => $this->faker->randomFloat(2, 0.80, 1.20),
            'supporter_strength' => $this->faker->randomFloat(2, 0.90, 1.30),
            'home_advantage_multiplier' => $this->faker->randomFloat(2, 1.00, 1.30),
        ];
    }
}

