<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Fixture\FixtureNotFoundException;
use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FixtureNotFoundExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    public function handle(Throwable $exception, Request $request): ?JsonResponse
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var FixtureNotFoundException $exception */
        return response()->json([
            'message' => $exception->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof FixtureNotFoundException && $this->isApiRequest($request);
    }
}
