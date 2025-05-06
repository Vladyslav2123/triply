<?php

namespace App\Actions\Profile;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Throwable;

class IncrementViewsAction
{
    /**
     * Increment the views count for a profile.
     *
     * @param  Profile  $profile  The profile to increment views for
     * @return bool Whether the increment was successful
     */
    public function execute(Profile $profile): bool
    {
        try {
            DB::transaction(function () use ($profile) {
                $profile->increment('views_count');
            });

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
