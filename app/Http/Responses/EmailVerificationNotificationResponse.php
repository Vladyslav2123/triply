<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationResponseContract;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationNotificationResponse implements EmailVerificationNotificationResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function toResponse($request): Response
    {
        return response()->json([
            'message' => 'Verification link sent',
        ], Response::HTTP_OK);
    }
}
