<?php

namespace App\Providers;

use App\MatchSimulation\Contracts\GoalGenerator;
use App\MatchSimulation\Contracts\MatchSimulator;
use App\MatchSimulation\Contracts\OutcomeCalculator;
use App\MatchSimulation\Contracts\RandomGenerator;
use App\MatchSimulation\DefaultMatchSimulator;
use App\MatchSimulation\Goals\SimpleGoalGenerator;
use App\MatchSimulation\Outcome\PowerBasedOutcomeCalculator;
use App\MatchSimulation\Random\NativeRandomGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RandomGenerator::class, NativeRandomGenerator::class);
        $this->app->bind(OutcomeCalculator::class, PowerBasedOutcomeCalculator::class);
        $this->app->bind(GoalGenerator::class, SimpleGoalGenerator::class);
        $this->app->bind(MatchSimulator::class, DefaultMatchSimulator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
