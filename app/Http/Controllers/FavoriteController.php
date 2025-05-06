<?php

namespace App\Http\Controllers;

use App\Actions\Favorite\DestroyFavorite;
use App\Actions\Favorite\StoreFavorite;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Http\Resources\FavoriteCollection;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Favorites",
 *     description="API Endpoints для роботи з обраними елементами"
 * )
 */
class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/favorites",
     *     operationId="getFavorites",
     *     summary="Отримати список обраних елементів користувача",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер сторінки",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Кількість елементів на сторінці",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCollection")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація")
     * )
     */
    public function index(Request $request): FavoriteCollection
    {
        $favorites = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->with('favoriteable')
            ->latest('added_at')
            ->paginate();

        return new FavoriteCollection($favorites);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws ValidationException
     *
     * @OA\Post(
     *     path="/api/v1/favorites",
     *     operationId="storeFavorite",
     *     summary="Додати елемент до обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteStoreRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Елемент додано до обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCreatedResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Елемент вже в обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCreatedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=422, description="Помилка валідації")
     * )
     */
    public function store(StoreFavoriteRequest $request, StoreFavorite $storeFavorite): JsonResponse
    {
        $favorite = $storeFavorite($request);

        return response()->json([
            'message' => $favorite->wasRecentlyCreated
                ? 'Added to favorites.'
                : 'Already in favorites.',
            'data' => new FavoriteResource($favorite),
        ], $favorite->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/v1/favorites/{id}",
     *     operationId="destroyFavorite",
     *     summary="Видалити елемент з обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID обраного елементу",
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Елемент видалено з обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteRemovedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=403, description="Заборонено"),
     *     @OA\Response(response=404, description="Елемент не знайдено")
     * )
     */
    public function destroy(Favorite $favorite, DestroyFavorite $destroyFavorite): JsonResponse
    {
        $this->authorize('delete', $favorite);

        return $destroyFavorite($favorite);
    }

    /**
     * Toggle favorite status for a listing or experience.
     *
     * @OA\Post(
     *     path="/api/v1/listings/{slug}/favorite",
     *     operationId="toggleListingFavorite",
     *     summary="Додати/видалити оголошення з обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug оголошення",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Додано до обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Favorite")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Видалено з обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteRemovedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=404, description="Оголошення не знайдено")
     * )
     *
     * @OA\Post(
     *     path="/api/v1/experiences/{slug}/favorite",
     *     operationId="toggleExperienceFavorite",
     *     summary="Додати/видалити враження з обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug враження",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Додано до обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Favorite")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Видалено з обраних",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteRemovedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=404, description="Враження не знайдено")
     * )
     */
    public function toggleFavorite(Request $request, $model): JsonResponse
    {
        $user = $request->user();
        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoriteable_id', $model->id)
            ->where('favoriteable_type', get_class($model))
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'message' => 'Removed from favorites.',
            ]);
        } else {
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'favoriteable_id' => $model->id,
                'favoriteable_type' => get_class($model),
                'added_at' => now(),
            ]);

            return response()->json([
                'id' => $favorite->id,
                'user_id' => $favorite->user_id,
                'favoriteable_id' => $favorite->favoriteable_id,
                'favoriteable_type' => class_basename($favorite->favoriteable_type),
                'created_at' => $favorite->added_at->toDate()->format('Y-m-d'),
            ], Response::HTTP_CREATED);
        }
    }

    /**
     * Check if an item is in the user's favorites.
     *
     * @OA\Get(
     *     path="/api/v1/listings/{slug}/is-favorited",
     *     operationId="isListingFavorited",
     *     summary="Перевірити, чи є оголошення в обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug оголошення",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCheckResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=404, description="Оголошення не знайдено")
     * )
     *
     * @OA\Get(
     *     path="/api/v1/experiences/{slug}/is-favorited",
     *     operationId="isExperienceFavorited",
     *     summary="Перевірити, чи є враження в обраних",
     *     tags={"Favorites"},
     *     security={"sessionAuth": {}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug враження",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCheckResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Необхідна аутентифікація"),
     *     @OA\Response(response=404, description="Враження не знайдено")
     * )
     */
    public function isFavorited(Request $request, $model): JsonResponse
    {
        $user = $request->user();
        $isFavorited = Favorite::where('user_id', $user->id)
            ->where('favoriteable_id', $model->id)
            ->where('favoriteable_type', get_class($model))
            ->exists();

        return response()->json([
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Get the number of users who have favorited an item.
     *
     * @OA\Get(
     *     path="/api/v1/listings/{slug}/favorite-count",
     *     operationId="getListingFavoriteCount",
     *     summary="Отримати кількість користувачів, які додали оголошення в обране",
     *     tags={"Favorites"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug оголошення",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCountResponse")
     *     ),
     *
     *     @OA\Response(response=404, description="Оголошення не знайдено")
     * )
     *
     * @OA\Get(
     *     path="/api/v1/experiences/{slug}/favorite-count",
     *     operationId="getExperienceFavoriteCount",
     *     summary="Отримати кількість користувачів, які додали враження в обране",
     *     tags={"Favorites"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug враження",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteCountResponse")
     *     ),
     *
     *     @OA\Response(response=404, description="Враження не знайдено")
     * )
     */
    public function getFavoriteCount($model): JsonResponse
    {
        $count = Favorite::where('favoriteable_id', $model->id)
            ->where('favoriteable_type', get_class($model))
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
