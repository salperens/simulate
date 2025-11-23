<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use App\Exceptions\Season\SeasonYearAlreadyExistsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SeasonYearAlreadyExistsExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    public function handle(Throwable $exception, Request $request): JsonResponse|null
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var SeasonYearAlreadyExistsException $exception */
        return response()->json([
            'message' => $exception->getMessage(),
        ], Response::HTTP_CONFLICT);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof SeasonYearAlreadyExistsException && $this->isApiRequest($request);
    }
}
