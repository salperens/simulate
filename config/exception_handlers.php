<?php

use App\Exceptions\Handlers\ActiveSeasonExistsExceptionHandler;
use App\Exceptions\Handlers\CannotCompleteSeasonExceptionHandler;
use App\Exceptions\Handlers\CannotPlayMatchesExceptionHandler;
use App\Exceptions\Handlers\CannotStartSeasonExceptionHandler;
use App\Exceptions\Handlers\DefaultExceptionHandler;
use App\Exceptions\Handlers\FixtureNotFoundExceptionHandler;
use App\Exceptions\Handlers\PredictionNotAvailableExceptionHandler;
use App\Exceptions\Handlers\SeasonNotFoundExceptionHandler;
use App\Exceptions\Handlers\SeasonYearAlreadyExistsExceptionHandler;
use App\Exceptions\Handlers\ValidationExceptionHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Exception Handlers
    |--------------------------------------------------------------------------
    |
    | This array defines the exception handlers that will be registered
    | in the application. Handlers are processed in order, and the first
    | handler that can handle an exception will be used.
    |
    */

    'handlers' => [
        ValidationExceptionHandler::class,
        PredictionNotAvailableExceptionHandler::class,
        FixtureNotFoundExceptionHandler::class,
        SeasonNotFoundExceptionHandler::class,
        ActiveSeasonExistsExceptionHandler::class,
        SeasonYearAlreadyExistsExceptionHandler::class,
        CannotStartSeasonExceptionHandler::class,
        CannotCompleteSeasonExceptionHandler::class,
        CannotPlayMatchesExceptionHandler::class,
        DefaultExceptionHandler::class,
    ],
];
