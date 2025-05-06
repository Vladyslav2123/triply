<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AlreadyAuthenticatedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Already authenticated',
            'authenticated' => true,
            'user' => auth()->user(),
        ], 200);
    }
}
