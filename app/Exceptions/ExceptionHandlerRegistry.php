<?php

namespace App\Exceptions;

use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

readonly class ExceptionHandlerRegistry
{
    /**
     * @param array<int, ExceptionHandler> $handlers
     */
    public function __construct(private array $handlers)
    {
    }

    public function handle(Throwable $exception, Request $request): mixed
    {
        foreach ($this->handlers as $handler) {
            if ($handler->shouldHandle($exception, $request)) {
                $response = $handler->handle($exception, $request);

                if ($response !== null) {
                    return $response;
                }
            }
        }

        return null;
    }
}
