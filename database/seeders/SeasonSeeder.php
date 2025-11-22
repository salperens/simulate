<?php

namespace Database\Seeders;

use App\Actions\Fixture\GenerateFixturesAction;
use App\Enums\Season\SeasonStatusEnum;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = (int)date('Y');

        $teams = Team::all();

        if ($teams->isEmpty()) {
            $this->command->warn('No teams found. Please run TeamSeeder first.');
            return;
        }

        $seasonName = "{$currentYear}-" . ($currentYear + 1) . " Season";

        $season = Season::firstOrCreate(
            ['year' => $currentYear],
            [
                'name'       => $seasonName,
                'status'     => SeasonStatusEnum::DRAFT,
                'start_date' => now()->startOfYear(),
                'end_date'   => now()->endOfYear(),
            ]
        );

        $season->teams()->syncWithoutDetaching($teams->pluck('id'));

        $this->command->info("Season {$season->name} created with {$teams->count()} teams.");

        if ($season->hasFixtures()) {
            $this->command->info('Fixtures already exist for this season.');
            return;
        }

        $this->command->info('Generating fixtures...');

        $generateFixturesAction = new GenerateFixturesAction();
        $fixtures = $generateFixturesAction->execute($season);

        $this->command->info("Generated {$fixtures->count()} fixtures for {$season->getTotalWeeks()} weeks.");
    }
}
