<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UpdatePreferencesAction
{
    /**
     * Update the user's language and currency preferences.
     *
     * @param  array  $data  Validated preferences data
     */
    public function execute(User $user, array $data): JsonResponse
    {
        $profile = $user->profile;

        $profile->update($data);

        return response()->json([
            'message' => 'Налаштування мови та валюти оновлено',
            'preferences' => [
                'language' => $profile->preferred_language,
                'currency' => $profile->preferred_currency,
            ],
        ]);
    }
}
