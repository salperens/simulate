<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name'                      => 'Galatasaray',
                'power_rating'              => 85,
                'goalkeeper_factor'         => 1.15,
                'supporter_strength'        => 1.20,
                'home_advantage_multiplier' => 1.15,
            ],
            [
                'name'                      => 'Fenerbahçe',
                'power_rating'              => 82,
                'goalkeeper_factor'         => 1.10,
                'supporter_strength'        => 1.18,
                'home_advantage_multiplier' => 1.12,
            ],
            [
                'name'                      => 'Beşiktaş',
                'power_rating'              => 78,
                'goalkeeper_factor'         => 1.05,
                'supporter_strength'        => 1.15,
                'home_advantage_multiplier' => 1.10,
            ],
            [
                'name'                      => 'Trabzonspor',
                'power_rating'              => 75,
                'goalkeeper_factor'         => 1.00,
                'supporter_strength'        => 1.12,
                'home_advantage_multiplier' => 1.08,
            ],
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
