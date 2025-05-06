<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements \Laravel\Fortify\Contracts\LogoutResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse|Response
    {
        return response()->json([
            'message' => 'Logged out successfully',
        ], Response::HTTP_NO_CONTENT);
    }
}
