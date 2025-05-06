<?php

namespace App\Http\Controllers;

use App\Actions\Review\CreateExperienceReview;
use App\Actions\Review\CreateListingReview;
use App\Actions\Review\CreateReview;
use App\Actions\Review\DeleteReview;
use App\Actions\Review\GetReview;
use App\Actions\Review\GetReviews;
use App\Actions\Review\UpdateReview;
use App\Http\Requests\Review\ExperienceReviewRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Resources\ReviewCollection;
use App\Http\Resources\ReviewResource;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Review;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * @OA\Tag(
 *     name="Reviews",
 *     description="API Endpoints для роботи з відгуками"
 * )
 */
class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/reviews",
     *     summary="Отримати всі відгуки з можливістю фільтрації",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
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
     *         name="listing_id",
     *         in="query",
     *         description="Фільтрувати за ID оголошення",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Поле для сортування (created_at, overall_rating)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Порядок сортування (asc, desc)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request, GetReviews $getReviews): JsonResponse
    {
        try {
            $reviews = $getReviews->execute($request);

            return response()->json(new ReviewCollection($reviews));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle exceptions
     */
    private function handleException(Exception $e): JsonResponse
    {
        Log::error('Review controller error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => $e->getMessage(),
            'status' => $e->getCode() ?: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        ], $e->getCode() ?: HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/v1/reviews",
     *     summary="Створити новий відгук",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані відгуку",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Відгук успішно створено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewValidationError")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreReviewRequest $request, CreateReview $createReview): JsonResponse
    {
        try {
            $this->authorize('create', Review::class);

            $validated = $request->validated();
            $validated['reviewer_id'] = auth()->id();

            $review = $createReview->execute($validated);

            return response()->json(new ReviewResource($review), HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/reviews/{id}",
     *     summary="Отримати конкретний відгук",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Відгук не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Review $review, GetReview $getReview): JsonResponse
    {
        try {
            $review = $getReview->execute($review);

            return response()->json(new ReviewResource($review));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/v1/reviews/{id}",
     *     summary="Оновити відгук",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані відгуку",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewUpdateRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Відгук успішно оновлено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewValidationError")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Відгук не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Заборонено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(UpdateReviewRequest $request, Review $review, UpdateReview $updateReview): JsonResponse
    {
        try {
            $this->authorize('update', $review);

            $validated = $request->validated();
            $updatedReview = $updateReview->execute($review, $validated);

            return response()->json(new ReviewResource($updatedReview));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/v1/reviews/{id}",
     *     summary="Видалити відгук",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Відгук успішно видалено"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Відгук не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Заборонено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Review $review, DeleteReview $deleteReview): JsonResponse|Response
    {
        try {
            $this->authorize('delete', $review);

            $deleteReview->execute($review);

            return response()->noContent();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => $e->getCode() ?: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
            ], $e->getCode() ?: HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get reviews for a specific listing.
     *
     * @OA\Get(
     *     path="/api/v1/listings/{slug}/reviews",
     *     summary="Отримати відгуки для конкретного оголошення",
     *     tags={"Reviews"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
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
     *         name="sort",
     *         in="query",
     *         description="Поле для сортування (created_at, overall_rating)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Порядок сортування (asc, desc)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function listingReviews(Request $request, Listing $listing, GetReviews $getReviews): JsonResponse
    {
        try {
            $reviews = $getReviews->forListing($listing, $request);

            return response()->json(new ReviewCollection($reviews));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a review for a specific listing.
     *
     * @OA\Post(
     *     path="/api/v1/listings/{slug}/reviews",
     *     summary="Створити відгук для конкретного оголошення",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані відгуку",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingReviewRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Відгук успішно створено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewValidationError")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function createListingReview(StoreReviewRequest $request, Listing $listing, CreateListingReview $createListingReview): JsonResponse
    {
        try {
            $this->authorize('createForListing', [Review::class, $listing]);

            $validated = $request->validated();
            $review = $createListingReview->execute($listing, $validated);

            return response()->json(new ReviewResource($review), HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get reviews for a specific experience.
     *
     * @OA\Get(
     *     path="/api/v1/experiences/{slug}/reviews",
     *     summary="Отримати відгуки для конкретного враження",
     *     tags={"Reviews"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
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
     *         name="sort",
     *         in="query",
     *         description="Поле для сортування (created_at, overall_rating)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Порядок сортування (asc, desc)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Враження не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function experienceReviews(Request $request, Experience $experience, GetReviews $getReviews): JsonResponse
    {
        try {
            $reviews = $getReviews->forExperience($experience, $request);

            return response()->json(new ReviewCollection($reviews));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a review for a specific experience.
     *
     * @OA\Post(
     *     path="/api/v1/experiences/{slug}/reviews",
     *     summary="Створити відгук для конкретного враження",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані відгуку",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceReviewRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Відгук успішно створено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ReviewValidationError")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Враження не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Необхідна аутентифікація",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function createExperienceReview(ExperienceReviewRequest $request, Experience $experience, CreateExperienceReview $createExperienceReview): JsonResponse
    {
        try {
            $this->authorize('createForExperience', [Review::class, $experience]);

            $validated = $request->validated();
            $review = $createExperienceReview->execute($experience, $validated);

            return response()->json(new ReviewResource($review), HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
