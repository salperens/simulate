<?php

use App\Exceptions\ExceptionHandlerRegistry;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handlerClasses = config('exception_handlers.handlers', []);
        $handlers = array_map(fn (string $class) => app($class), $handlerClasses);

        $registry = new ExceptionHandlerRegistry($handlers);

        $exceptions->render(function (Throwable $e, Request $request) use ($registry) {
            return $registry->handle($e, $request);
        });
    })->create();
