<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UpdateNotificationsAction
{
    /**
     * Update the user's notification settings.
     *
     * @param  array  $data  Validated notification settings
     */
    public function execute(User $user, array $data): JsonResponse
    {
        $profile = $user->profile;

        $profile->update($data);

        return response()->json([
            'message' => 'Налаштування сповіщень оновлено',
            'notifications' => [
                'email' => $profile->email_notifications,
                'sms' => $profile->sms_notifications,
            ],
        ]);
    }
}
