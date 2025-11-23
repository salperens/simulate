<?php

namespace App\Exceptions\Handlers\Contracts;

use Illuminate\Http\Request;
use Throwable;

interface ExceptionHandler
{
    public function handle(Throwable $exception, Request $request): mixed;

    public function shouldHandle(Throwable $exception, Request $request): bool;
}
