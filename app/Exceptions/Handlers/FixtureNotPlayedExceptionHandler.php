<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Fixture\FixtureNotPlayedException;
use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FixtureNotPlayedExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    public function handle(Throwable $exception, Request $request): JsonResponse|null
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var FixtureNotPlayedException $exception */
        return response()->json([
            'message' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof FixtureNotPlayedException && $this->isApiRequest($request);
    }
}
