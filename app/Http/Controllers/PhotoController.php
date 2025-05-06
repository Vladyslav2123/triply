<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PhotoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/photos",
     *     operationId="getPhotos",
     *     summary="Отримати список фотографій",
     *     description="Отримати список всіх фотографій з пагінацією",
     *     tags={"Photos"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PhotoCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизовано",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', Photo::class);

        $photos = Photo::query()
            ->with('photoable')
            ->latest('uploaded_at')
            ->paginate();

        return PhotoResource::collection($photos);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/photos",
     *     operationId="storePhoto",
     *     summary="Завантажити нову фотографію",
     *     description="Завантажити нову фотографію для профілю, житла або враження",
     *     tags={"Photos"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані для завантаження фотографії",
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(ref="#/components/schemas/PhotoUploadRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Фотографія успішно завантажена",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PhotoResponse")
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
     *         response=403,
     *         description="Заборонено",
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
    public function store(StorePhotoRequest $request): JsonResponse
    {
        $photoableModel = $request->getPhotoableModel();
        $this->authorize('create', [Photo::class, $photoableModel]);

        $photo = $this->createPhoto->execute(
            $photoableModel,
            $request->file('file'),
            $request->input('directory')
        );

        return response()->json(
            new PhotoResource($photo),
            Response::HTTP_CREATED
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/photos/{id}",
     *     operationId="getPhoto",
     *     summary="Отримати деталі фотографії",
     *     description="Отримати детальну інформацію про конкретну фотографію",
     *     tags={"Photos"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID фотографії",
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PhotoResponse")
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
    public function show(Photo $photo): PhotoResource
    {
        $this->authorize('view', $photo);

        return new PhotoResource($photo->load('photoable'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/photos/{id}",
     *     operationId="deletePhoto",
     *     summary="Видалити фотографію",
     *     description="Видалити фотографію за її ID",
     *     tags={"Photos"},
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID фотографії",
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Фотографію успішно видалено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизовано",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Заборонено",
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
    public function destroy(Photo $photo): JsonResponse
    {
        $this->authorize('delete', $photo);

        $photo->forceDelete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
