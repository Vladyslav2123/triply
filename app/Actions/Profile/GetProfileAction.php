<?php

namespace App\Actions\Profile;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GetProfileAction
{
    /**
     * Get the user's profile.
     */
    public function execute(User $user): JsonResponse
    {
        $profile = $user->profile;

        return response()->json(new ProfileResource($profile));
    }
}
