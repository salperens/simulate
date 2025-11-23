<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidationExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    private const ERROR_MESSAGE = 'Validation failed';

    public function handle(Throwable $exception, Request $request): ?JsonResponse
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        /** @var ValidationException $exception */
        return response()->json([
            'message' => self::ERROR_MESSAGE,
            'errors'  => $exception->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $exception instanceof ValidationException && $this->isApiRequest($request);
    }
}

