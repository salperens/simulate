<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fixture>
 */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'week_number' => $this->faker->numberBetween(1, 6),
            'home_score' => null,
            'away_score' => null,
            'played_at' => null,
        ];
    }

    public function played(): static
    {
        return $this->state(fn (array $attributes) => [
            'home_score' => $this->faker->numberBetween(0, 5),
            'away_score' => $this->faker->numberBetween(0, 5),
            'played_at' => now(),
        ]);
    }
}

