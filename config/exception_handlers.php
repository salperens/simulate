<?php

use App\Exceptions\Handlers\DefaultExceptionHandler;
use App\Exceptions\Handlers\SeasonNotFoundExceptionHandler;

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
        SeasonNotFoundExceptionHandler::class,
        DefaultExceptionHandler::class,
    ],
];
