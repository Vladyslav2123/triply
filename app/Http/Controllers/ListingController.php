<?php

namespace App\Http\Controllers;

use App\Actions\Listing\CreateListing;
use App\Actions\Listing\DeleteListing;
use App\Actions\Listing\FilterListings;
use App\Actions\Listing\PublishListing;
use App\Actions\Listing\UnpublishListing;
use App\Actions\Listing\UpdateListing;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Resources\ListingDetailResource;
use App\Http\Resources\ListingResource;
use App\Http\Validators\ListingValidators;
use App\Models\Listing;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;
use Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Listings",
 *     description="API Endpoints для роботи з оголошеннями"
 * )
 */
class ListingController extends Controller
{
    public function __construct(
        private readonly ListingValidators $validators,
        private readonly FilterListings $filterListings,
        private readonly CreateListing $createListing,
        private readonly UpdateListing $updateListing,
        private readonly DeleteListing $deleteListing,
        private readonly PublishListing $publishListing,
        private readonly UnpublishListing $unpublishListing,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/listings",
     *     summary="Отримати список оголошень",
     *     tags={"Listings"},
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
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Сортування (price_asc, price_desc, rating_asc, rating_desc, created_at_asc, created_at_desc, title_asc, title_desc, reviews_count_asc, reviews_count_desc, popularity)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"price_asc", "price_desc", "rating_asc", "rating_desc", "created_at_asc", "created_at_desc", "title_asc", "title_desc", "reviews_count_asc", "reviews_count_desc", "popularity"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_min",
     *         in="query",
     *         description="Мінімальна ціна",
     *         required=false,
     *
     *         @OA\Schema(type="number")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_max",
     *         in="query",
     *         description="Максимальна ціна",
     *         required=false,
     *
     *         @OA\Schema(type="number")
     *     ),
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип нерухомості",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_rating",
     *         in="query",
     *         description="Мінімальний рейтинг",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Місцезнаходження (latitude, longitude, radius)",
     *         required=false,
     *
     *         @OA\Schema(type="object")
     *     ),
     *
     *     @OA\Parameter(
     *         name="check_in",
     *         in="query",
     *         description="Дата заїзду (формат: Y-m-d)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="check_out",
     *         in="query",
     *         description="Дата виїзду (формат: Y-m-d)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="guests",
     *         in="query",
     *         description="Кількість гостей",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="amenities",
     *         in="query",
     *         description="Зручності",
     *         required=false,
     *
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *
     *     @OA\Parameter(
     *         name="accessibility_features",
     *         in="query",
     *         description="Особливості доступності",
     *         required=false,
     *
     *         @OA\Schema(type="object")
     *     ),
     *
     *     @OA\Parameter(
     *         name="property_size_min",
     *         in="query",
     *         description="Мінімальна площа нерухомості",
     *         required=false,
     *
     *         @OA\Schema(type="number")
     *     ),
     *
     *     @OA\Parameter(
     *         name="property_size_max",
     *         in="query",
     *         description="Максимальна площа нерухомості",
     *         required=false,
     *
     *         @OA\Schema(type="number")
     *     ),
     *
     *     @OA\Parameter(
     *         name="year_built_min",
     *         in="query",
     *         description="Мінімальний рік побудови",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="year_built_max",
     *         in="query",
     *         description="Максимальний рік побудови",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="guest_safety",
     *         in="query",
     *         description="Особливості безпеки гостей",
     *         required=false,
     *
     *         @OA\Schema(type="object")
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Пошук за назвою або описом",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingCollection")
     *     )
     * )
     *
     * @throws Exception
     */
    public function index(Request $request): ResourceCollection
    {
        Log::info('Listing index called with params:', $request->query());

        $this->validators->validate($request);
        $listings = $this->filterListings->execute($request->query())
            ->through(function ($listing) {
                return $listing->load(['photos']);
            });

        return ListingResource::collection($listings)
            ->additional(['meta' => ['filters' => $request->query()]]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/listings",
     *     summary="Створити нове оголошення",
     *     tags={"Listings"},
     *     security={},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Оголошення створено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingDetail")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @throws Exception
     */
    public function store(StoreListingRequest $request): JsonResponse
    {
        $listing = $this->createListing->execute($request->validated());

        return (new ListingDetailResource($listing))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/listings/{slug}",
     *     summary="Отримати детальну інформацію про оголошення",
     *     tags={"Listings"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ListingDetail")
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
    public function show(Listing $listing): ListingDetailResource
    {
        return new ListingDetailResource(
            $listing->load([
                'host.profile',
                'photos',
                'availability',
                'reservations' => fn ($query) => $query->active(),
            ])
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/listings/{slug}",
     *     summary="Оновити існуюче оголошення",
     *     tags={"Listings"},
     *     security={},
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
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Оголошення оновлено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingDetail")
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
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @throws Exception
     */
    public function update(UpdateListingRequest $request, Listing $listing): ListingDetailResource
    {
        $this->authorize('update', $listing);
        $updatedListing = $this->updateListing->execute($listing, $request->validated());

        return new ListingDetailResource($updatedListing);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/listings/{slug}",
     *     summary="Видалити оголошення",
     *     tags={"Listings"},
     *     security={},
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
     *         response=204,
     *         description="Оголошення видалено"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Заборонено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @throws Exception
     */
    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);
        $this->deleteListing->execute($listing);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/hosts/{hostId}/listings",
     *     operationId="getHostListings",
     *     summary="Отримати список оголошень конкретного господаря",
     *     tags={"Listings"},
     *
     *     @OA\Parameter(
     *         name="hostId",
     *         in="path",
     *         required=true,
     *         description="ID господаря",
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingCollection")
     *     )
     * )
     */
    public function byHost(Request $request, string $hostId): ResourceCollection
    {
        $listings = Listing::query()
            ->where('host_id', $hostId)
            ->with(['photos', 'availability'])
            ->withCount(['reservations'])
            ->withCount(['reservations as reviews_count' => function ($query) {
                $query->whereHas('review');
            }])
            ->withAvgRating()
            ->latest()
            ->paginate();

        return ListingResource::collection($listings);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/listings/featured",
     *     operationId="getFeaturedListings",
     *     summary="Отримати список рекомендованих оголошень",
     *     tags={"Listings"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успішна операція",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingCollection")
     *     )
     * )
     */
    public function featured(): ResourceCollection
    {
        $listings = Cache::remember('featured_listings', 3600, function () {
            return Listing::query()
                ->featured()
                ->with(['photos', 'host.profile'])
                ->withAvgRating()
                ->withReviewsCount()
                ->latest()
                ->limit(6)
                ->get();
        });

        return ListingResource::collection($listings);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/listings/{slug}/similar",
     *     operationId="getSimilarListings",
     *     summary="Отримати список схожих оголошень",
     *     tags={"Listings"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ListingCollection")
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
    public function similar(Listing $listing): ResourceCollection
    {
        $cacheKey = 'similar_listings_'.$listing->id;

        $similarListings = Cache::remember($cacheKey, 3600, function () use ($listing) {
            return Listing::query()
                ->where('listings.type', $listing->type)
                ->where('listings.id', '!=', $listing->id)
                ->where('listings.price_per_night', '>=', $listing->price_per_night->getAmount() * 1)
                ->where('listings.price_per_night', '<=', $listing->price_per_night->getAmount() * 2)
                ->with(['photos', 'host.profile'])
                ->withAvgRating()
                ->inRandomOrder()
                ->limit(3)
                ->get();
        });

        return ListingResource::collection($similarListings);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/listings/{slug}/publish",
     *     summary="Опублікувати оголошення",
     *     tags={"Listings"},
     *     security={},
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
     *         description="Оголошення опубліковано",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingDetail")
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
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @throws Exception
     */
    public function publish(Listing $listing): ListingDetailResource
    {
        $this->authorize('publish', $listing);
        $publishedListing = $this->publishListing->execute($listing);

        return new ListingDetailResource($publishedListing);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/listings/{slug}/unpublish",
     *     summary="Зняти оголошення з публікації",
     *     tags={"Listings"},
     *     security={},
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
     *         description="Оголошення знято з публікації",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ListingDetail")
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
     *         description="Оголошення не знайдено",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @throws Exception
     */
    public function unpublish(Listing $listing): ListingDetailResource
    {
        $this->authorize('unpublish', $listing);
        $unpublishedListing = $this->unpublishListing->execute($listing);

        return new ListingDetailResource($unpublishedListing);
    }
}
