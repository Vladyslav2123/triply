<?php

namespace App\Http\Controllers;

use App\Actions\Profile\DeleteProfileAction;
use App\Actions\Profile\GetProfileAction;
use App\Actions\Profile\IncrementViewsAction;
use App\Actions\Profile\UpdateNotificationsAction;
use App\Actions\Profile\UpdatePreferencesAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Actions\Profile\VerifyProfileAction;
use App\Http\Requests\Profile\UpdateNotificationsRequest;
use App\Http\Requests\Profile\UpdatePreferencesRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\VerifyProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly GetProfileAction $getProfileAction,
        private readonly IncrementViewsAction $incrementViewsAction,
        private readonly UpdateNotificationsAction $updateNotificationsAction,
        private readonly UpdatePreferencesAction $updatePreferencesAction,
        private readonly UpdateProfileAction $updateProfileAction,
        private readonly DeleteProfileAction $deleteProfileAction,
        private readonly VerifyProfileAction $verifyProfileAction,
    ) {}

    /**
     * Get authenticated user's profile.
     *
     * @OA\Get(
     *     path="/api/v1/profile",
     *     operationId="getOwnProfile",
     *     tags={"Profile"},
     *     summary="Get own profile",
     *     description="Returns the authenticated user's profile",
     *     security={{"sessionAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProfileResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        if (! Auth::user()->profile) {
            return response()->json([
                'message' => 'Профіль не знайдено',
            ], 404);
        }

        return $this->getProfileAction->execute(Auth::user());
    }

    /**
     * Get a specific user's profile.
     *
     * @OA\Get(
     *     path="/api/v1/profiles/{id}",
     *     operationId="getProfile",
     *     tags={"Profile"},
     *     summary="Get user profile by ID",
     *     description="Returns a user's profile by ID and increments view count if viewing other's profile",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Profile ID",
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProfileResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function showById($id): JsonResponse
    {
        if ($id instanceof User) {
            $user = $id;
            $profile = $user->profile;
        } else {
            $profile = Profile::findOrFail($id);
            $user = $profile->user;
        }

        if (! $profile) {
            return response()->json([
                'message' => 'Профіль не знайдено',
            ], 404);
        }

        if (Auth::check() && Auth::id() !== $profile->user_id) {
            $this->incrementViewsAction->execute($profile);
        }

        return $this->getProfileAction->execute($user);
    }

    /**
     * Update notification settings.
     *
     * @OA\Patch(
     *     path="/api/v1/profile/notifications",
     *     operationId="updateNotifications",
     *     tags={"Profile"},
     *     summary="Update notification preferences",
     *     description="Updates email and SMS notification settings",
     *     security={{"sessionAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email_notifications", "sms_notifications"},
     *
     *             @OA\Property(property="email_notifications", type="boolean"),
     *             @OA\Property(property="sms_notifications", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Settings updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="notifications",
     *                 type="object",
     *                 @OA\Property(property="email", type="boolean"),
     *                 @OA\Property(property="sms", type="boolean")
     *             )
     *         )
     *     )
     * )
     */
    public function updateNotifications(UpdateNotificationsRequest $request): JsonResponse
    {
        return $this->updateNotificationsAction->execute(
            Auth::user(),
            $request->validated()
        );
    }

    /**
     * Update language and currency preferences.
     *
     * @OA\Patch(
     *     path="/api/v1/profile/preferences",
     *     operationId="updatePreferences",
     *     tags={"Profile"},
     *     summary="Update language and currency preferences",
     *     security={{"sessionAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"preferred_language", "preferred_currency"},
     *
     *             @OA\Property(property="preferred_language", type="string", example="uk"),
     *             @OA\Property(property="preferred_currency", type="string", example="UAH")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Preferences updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="preferences",
     *                 type="object",
     *                 @OA\Property(property="language", type="string"),
     *                 @OA\Property(property="currency", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        return $this->updatePreferencesAction->execute(
            Auth::user(),
            $request->validated()
        );
    }

    /**
     * Verify user profile.
     *
     * @OA\Patch(
     *     path="/api/v1/profile/verify",
     *     operationId="verifyProfile",
     *     tags={"Profile"},
     *     summary="Verify user profile",
     *     security={{"sessionAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"verification_method"},
     *
     *             @OA\Property(
     *                 property="verification_method",
     *                 type="string",
     *                 enum={"manual", "document", "phone", "email"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile verified successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="profile", ref="#/components/schemas/ProfileResponse")
     *         )
     *     )
     * )
     */
    public function verify(VerifyProfileRequest $request): JsonResponse
    {
        return $this->verifyProfileAction->execute(
            Auth::user(),
            $request->validated()
        );
    }

    /**
     * Update user profile.
     *
     * @OA\Put(
     *     path="/api/v1/profile",
     *     operationId="updateProfile",
     *     tags={"Profile"},
     *     summary="Update user profile",
     *     description="Updates the authenticated user's profile information",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="birth_date", type="string", format="date"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}),
     *             @OA\Property(property="about", type="string"),
     *             @OA\Property(property="languages", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="work", type="string"),
     *             @OA\Property(property="company", type="string"),
     *             @OA\Property(property="country", type="string"),
     *             @OA\Property(property="city", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="profile", ref="#/components/schemas/ProfileResponse")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->updateProfileAction->execute(
            Auth::user(),
            $request->validated()
        );
    }

    /**
     * Delete user profile.
     *
     * @OA\Delete(
     *     path="/api/v1/profile",
     *     operationId="deleteProfile",
     *     tags={"Profile"},
     *     summary="Delete user profile",
     *     description="Soft deletes the authenticated user's profile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(): JsonResponse
    {
        return $this->deleteProfileAction->execute(Auth::user());
    }
}
