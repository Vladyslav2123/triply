<?php

namespace App\Http\Controllers;

use App\Actions\Photo\CreatePhoto;
use App\Actions\Photo\DeletePhoto;
use App\Constants\PhotoConstants;
use App\Http\Requests\Profile\UploadProfilePhotoRequest;
use App\Http\Resources\PhotoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProfilePhotoController extends Controller
{
    public function __construct(
        private readonly CreatePhoto $createPhoto,
        private readonly DeletePhoto $deletePhoto,
    ) {}

    /**
     * Upload a photo for the authenticated user's profile.
     *
     * @OA\Post(
     *     path="/api/v1/profile/photo",
     *     operationId="uploadProfilePhoto",
     *     tags={"Profile"},
     *     summary="Завантажити фото профілю",
     *     description="Завантажити нову фотографію для профілю авторизованого користувача",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані для завантаження фотографії профілю",
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(ref="#/components/schemas/ProfilePhotoUploadRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Фотографію профілю успішно завантажено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProfilePhotoResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизовано",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(UploadProfilePhotoRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($profile->photo) {
            $this->deletePhoto->execute($profile->photo);
        }

        $photo = $this->createPhoto->execute(
            $profile,
            $request->file('photo'),
            PhotoConstants::DIRECTORY_USERS
        );

        return response()->json([
            'message' => 'Фото профілю успішно завантажено',
            'photo' => new PhotoResource($photo),
        ], Response::HTTP_OK);
    }

    /**
     * Delete the authenticated user's profile photo.
     *
     * @OA\Delete(
     *     path="/api/v1/profile/photo",
     *     operationId="deleteProfilePhoto",
     *     tags={"Profile"},
     *     summary="Видалити фото профілю",
     *     description="Видалити фотографію профілю авторизованого користувача",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Фотографію профілю успішно видалено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PhotoDeleteResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизовано",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Фотографію не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (! $profile->photo) {
            return response()->json([
                'message' => 'Фото профілю не знайдено',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->deletePhoto->execute($profile->photo);

        return response()->json([
            'message' => 'Фото профілю успішно видалено',
        ], Response::HTTP_OK);
    }
}
