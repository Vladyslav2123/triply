<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DeleteProfileAction
{
    /**
     * Delete a user's profile.
     *
     * @param  User  $user  The user whose profile to delete
     * @return JsonResponse Response indicating success or failure
     */
    public function execute(User $user): JsonResponse
    {
        try {
            if (! $user->profile) {
                return response()->json([
                    'message' => 'Профіль не знайдено',
                ], Response::HTTP_NOT_FOUND);
            }

            DB::transaction(function () use ($user) {
                $user->profile->delete();
            });

            return response()->json([
                'message' => 'Профіль успішно видалено',
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Помилка при видаленні профілю',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
