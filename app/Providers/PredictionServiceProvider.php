<?php

namespace App\Providers;

use App\MatchSimulation\Contracts\MatchSimulator;
use App\Prediction\Algorithms\MonteCarlo\ChampionFinder;
use App\Prediction\Algorithms\MonteCarlo\EarlyTerminationChecker;
use App\Prediction\Algorithms\MonteCarlo\FixtureSimulator;
use App\Prediction\Algorithms\MonteCarlo\ProbabilityCalculator;
use App\Prediction\Algorithms\MonteCarlo\StandingsCalculator;
use App\Prediction\Contracts\PredictionAlgorithm;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class PredictionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerMonteCarloDependencies();
        $this->registerPredictionAlgorithm();
    }

    /**
     * Register Monte Carlo algorithm dependencies.
     * These bindings are only needed for MonteCarloPredictionAlgorithm.
     * Other algorithms can register their own dependencies if needed.
     */
    private function registerMonteCarloDependencies(): void
    {
        $this->app->bind(FixtureSimulator::class, function (Application $app) {
            return new FixtureSimulator(
                matchSimulator: $app->make(MatchSimulator::class),
            );
        });

        $this->app->singleton(StandingsCalculator::class);
        $this->app->singleton(ChampionFinder::class);
        $this->app->singleton(EarlyTerminationChecker::class);

        $this->app->bind(ProbabilityCalculator::class, function (Application $app) {
            $simulationCount = $this->getSimulationCount();
            return new ProbabilityCalculator($simulationCount);
        });
    }

    /**
     * Register the prediction algorithm binding.
     * The algorithm class is resolved dynamically from config at runtime.
     * Singleton binding ensures the same instance is reused throughout the request lifecycle.
     */
    private function registerPredictionAlgorithm(): void
    {
        $this->app->singleton(
            PredictionAlgorithm::class,
            fn(Application $app) => $this->resolvePredictionAlgorithm($app),
        );
    }

    /**
     * Resolve the prediction algorithm instance.
     * The algorithm class is read from config at resolve time, not register time.
     * Container will automatically resolve the algorithm's dependencies.
     */
    private function resolvePredictionAlgorithm(Application $app): PredictionAlgorithm
    {
        $algorithmClass = $this->getAlgorithmClass();
        $this->validateAlgorithmClass($algorithmClass);

        // Each algorithm class should be resolvable via dependency injection
        // If the algorithm needs specific dependencies, they should be registered
        // in their respective service providers or in this provider's register methods
        return $app->make($algorithmClass);
    }

    /**
     * Get the algorithm class from configuration.
     */
    private function getAlgorithmClass(): string
    {
        $algorithmClass = config('prediction.default_algorithm');

        if (empty($algorithmClass)) {
            throw new InvalidArgumentException(
                'Prediction algorithm class is not configured. Please set prediction.default_algorithm in config/prediction.php'
            );
        }

        return $algorithmClass;
    }

    /**
     * Get the simulation count from configuration.
     */
    private function getSimulationCount(): int
    {
        $simulationCount = config('prediction.monte_carlo.simulation_count');

        if (!is_int($simulationCount) || $simulationCount < 1) {
            throw new InvalidArgumentException(
                'Monte Carlo simulation count must be a positive integer. Please set prediction.monte_carlo.simulation_count in config/prediction.php'
            );
        }

        return $simulationCount;
    }

    /**
     * Validate that the algorithm class implements PredictionAlgorithm interface.
     */
    private function validateAlgorithmClass(string $algorithmClass): void
    {
        if (!class_exists($algorithmClass)) {
            throw new InvalidArgumentException(
                "Prediction algorithm class '{$algorithmClass}' does not exist."
            );
        }

        if (!is_subclass_of($algorithmClass, PredictionAlgorithm::class)) {
            throw new InvalidArgumentException(
                "Prediction algorithm class '{$algorithmClass}' must implement " . PredictionAlgorithm::class
            );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
