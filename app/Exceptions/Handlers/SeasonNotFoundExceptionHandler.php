<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use App\Exceptions\League\SeasonNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SeasonNotFoundExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $exception, Request $request): JsonResponse|null
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var SeasonNotFoundException $exception */
        return response()->json([
            'message' => $exception->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof SeasonNotFoundException && $request->is('api/*');
    }
}

