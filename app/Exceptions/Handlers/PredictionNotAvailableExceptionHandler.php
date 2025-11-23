<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use App\Exceptions\Prediction\PredictionNotAvailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PredictionNotAvailableExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    public function handle(Throwable $exception, Request $request): ?JsonResponse
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var PredictionNotAvailableException $exception */
        return response()->json([
            'message' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof PredictionNotAvailableException && $this->isApiRequest($request);
    }
}
