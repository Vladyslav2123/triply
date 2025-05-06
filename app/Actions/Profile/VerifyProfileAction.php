<?php

namespace App\Actions\Profile;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyProfileAction
{
    /**
     * Verify a user's profile.
     *
     * @param  User  $user  The user whose profile to verify
     * @param  array  $data  Verification data including method
     * @return JsonResponse The verified profile
     */
    public function execute(User $user, array $data): JsonResponse
    {
        try {
            if (! $user->profile) {
                return response()->json([
                    'message' => 'Профіль не знайдено',
                ], Response::HTTP_NOT_FOUND);
            }

            $profile = DB::transaction(function () use ($user, $data) {
                $profile = $user->profile;

                $profile->is_verified = true;
                $profile->verified_at = now();
                $profile->verification_method = $data['verification_method'] ?? 'manual';
                $profile->save();

                return $profile;
            });

            return response()->json([
                'message' => 'Профіль успішно верифіковано',
                'profile' => new ProfileResource($profile),
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Помилка при верифікації профілю',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
