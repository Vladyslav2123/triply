<?php

namespace App\Actions\Profile;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProfileAction
{
    /**
     * Update the user's profile.
     *
     * @param  array  $data  Validated profile data
     * @param  bool  $handleEmailVerification  Whether to handle email verification
     *
     * @throws Throwable
     */
    public function execute(User $user, array $data, bool $handleEmailVerification = true): JsonResponse
    {
        $profile = $user->getOrCreateProfile();
        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        DB::transaction(function () use ($user, $profile, $data, $emailChanged, $handleEmailVerification) {
            // Update profile fields - exclude fields that belong to the User model
            $profile->update(collect($data)->except(['email', 'phone'])->toArray());

            $userData = [];

            if (isset($data['phone'])) {
                $userData['phone'] = $data['phone'];
            }

            if ($emailChanged) {
                $userData['email'] = $data['email'];

                if ($handleEmailVerification && $user instanceof MustVerifyEmail) {
                    $userData['email_verified_at'] = null;
                }
            }

            if (! empty($userData)) {
                $user->forceFill($userData)->save();
            }

            if ($emailChanged && $handleEmailVerification && $user instanceof MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }
        });

        return response()->json([
            'message' => 'Профіль успішно оновлено',
            'profile' => new ProfileResource($profile->fresh()),
        ]);
    }
}
