<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateLastActiveAction
{
    /**
     * Update the last active timestamp for a user's profile.
     *
     * @param  User  $user  The user whose profile to update
     * @return bool Whether the update was successful
     */
    public function execute(User $user): bool
    {
        try {
            if (! $user->profile) {
                return false;
            }

            DB::transaction(function () use ($user) {
                $profile = $user->profile;
                $profile->last_active_at = now();
                $profile->save();
            });

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
