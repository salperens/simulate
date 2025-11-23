<?php

namespace App\Exceptions\Handlers\Concerns;

use Illuminate\Http\Request;

trait HandlesApiRequests
{
    /**
     * Check if the request is an API request.
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*');
    }
}

