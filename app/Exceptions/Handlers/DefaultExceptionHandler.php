<?php

namespace App\Exceptions\Handlers;

use App\Exceptions\Handlers\Concerns\HandlesApiRequests;
use App\Exceptions\Handlers\Contracts\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DefaultExceptionHandler implements ExceptionHandler
{
    use HandlesApiRequests;

    private const DEFAULT_EXCEPTION_MESSAGE = 'An error occurred';
    private const DEFAULT_CODE = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function handle(Throwable $exception, Request $request): ?JsonResponse
    {
        if (!$this->shouldHandle($exception, $request)) {
            return null;
        }

        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);

        return response()->json([
            'message' => $message,
        ], $statusCode);
    }

    public function shouldHandle(Throwable $exception, Request $request): bool
    {
        return $this->isApiRequest($request);
    }

    private function getStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        if ($this->isCodeValid($exception->getCode())) {
            return $exception->getCode();
        }

        return self::DEFAULT_CODE;
    }

    private function isCodeValid(int $code): bool
    {
        return $code >= 400 && $code < 600;
    }

    private function getMessage(Throwable $exception): string
    {
        return $exception->getMessage() ?: self::DEFAULT_EXCEPTION_MESSAGE;
    }
}
