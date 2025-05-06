<?php

namespace App\Http\Controllers;

use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\Http\Requests\Experience\StoreExperienceRequest;
use App\Http\Requests\Experience\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use App\Models\User;
use App\QueryBuilders\Experience\ExperienceQueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/experiences",
     *     operationId="getExperiences",
     *     summary="Get list of experiences with filtering and sorting options",
     *     tags={"Experiences"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by experience category",
     *
     *         @OA\Schema(type="string", enum={"art_and_design", "food_and_drink", "nature_and_outdoors", "sports_and_activities", "wellness", "music", "history_and_culture", "nightlife", "workshops", "animals", "photography", "local_life"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter by city",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filter by language",
     *
     *         @OA\Schema(type="string", enum={"en", "uk", "pl", "de", "fr", "es", "it", "pt", "nl", "ru", "zh", "ja", "ko", "ar", "hi", "tr"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_min",
     *         in="query",
     *         description="Minimum price",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_max",
     *         in="query",
     *         description="Maximum price",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="group_size",
     *         in="query",
     *         description="Minimum group size",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_rating",
     *         in="query",
     *         description="Minimum rating",
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="physical_activity",
     *         in="query",
     *         description="Physical activity level",
     *
     *         @OA\Schema(type="string", enum={"low", "moderate", "high"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="skill_level",
     *         in="query",
     *         description="Skill level",
     *
     *         @OA\Schema(type="string", enum={"beginner", "intermediate", "advanced"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for availability",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for availability",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Filter featured experiences",
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order",
     *
     *         @OA\Schema(type="string", enum={"price_asc", "price_desc", "rating", "newest", "popularity"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceCollection")
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $queryBuilder = new ExperienceQueryBuilder;
        $queryBuilder->published();

        if ($request->has('category')) {
            $queryBuilder->withCategory(ExperienceType::from($request->category));
        }

        if ($request->has('city')) {
            $queryBuilder->inCity($request->city);
        }

        if ($request->has('language')) {
            $queryBuilder->withLanguage(Language::from($request->language));
        }

        if ($request->has('price_min') && $request->has('price_max')) {
            $queryBuilder->priceRange($request->price_min, $request->price_max);
        }

        if ($request->has('group_size')) {
            $queryBuilder->withGroupSize($request->group_size);
        }

        if ($request->has('min_rating')) {
            $queryBuilder->withMinRating($request->min_rating);
        }

        if ($request->has('physical_activity')) {
            $queryBuilder->withPhysicalActivityLevel(PhysicalActivityLevel::from($request->physical_activity));
        }

        if ($request->has('skill_level')) {
            $queryBuilder->withSkillLevel(SkillLevel::from($request->skill_level));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $queryBuilder->availableBetween($request->start_date, $request->end_date);
        }

        if ($request->boolean('featured')) {
            $queryBuilder->featured();
        }

        if ($request->has('search')) {
            $queryBuilder->search($request->search);
        }

        if ($request->has('sort')) {
            match ($request->sort) {
                'price_asc' => $queryBuilder->sortByPriceAsc(),
                'price_desc' => $queryBuilder->sortByPriceDesc(),
                'rating' => $queryBuilder->sortByRating(),
                'newest' => $queryBuilder->sortByNewest(),
                'popularity' => $queryBuilder->sortByPopularity(),
                default => $queryBuilder->sortByRating(),
            };
        } else {
            $queryBuilder->sortByRating();
        }

        $queryBuilder->with(['host', 'photos']);

        return ExperienceResource::collection(
            $queryBuilder->getQuery()->paginate($request->per_page ?? 10)
        )->additional(['meta' => ['filters' => $request->query()]]);
    }

    /**
     * Display a listing of featured experiences.
     *
     * @OA\Get(
     *     path="/api/v1/experiences/featured",
     *     operationId="getFeaturedExperiences",
     *     summary="Get list of featured experiences",
     *     tags={"Experiences"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceCollection")
     *     )
     * )
     */
    public function featured(): AnonymousResourceCollection
    {
        $experiences = Cache::remember('featured_experiences', 3600, function () {
            return Experience::where('is_featured', true)
                ->where('status', ExperienceStatus::PUBLISHED)
                ->with(['host', 'photos'])
                ->take(10)
                ->get();
        });

        return ExperienceResource::collection($experiences);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/v1/experiences",
     *     operationId="storeExperience",
     *     summary="Create a new experience",
     *     tags={"Experiences"},
     *     security={{ "sessionAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreExperienceRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Experience created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceResource")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     )
     * )
     */
    public function store(StoreExperienceRequest $request): ExperienceResource
    {
        $experience = new Experience($request->validated());
        $experience->host_id = $request->user()->id;
        $experience->status = ExperienceStatus::DRAFT;
        $experience->save();

        return new ExperienceResource($experience);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/experiences/{experience}",
     *     operationId="showExperience",
     *     summary="Get experience details",
     *     tags={"Experiences"},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="Experience ID or slug",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceResource")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Experience not found"
     *     )
     * )
     */
    public function show(Experience $experience): ExperienceResource
    {
        $experience->increment('views_count');

        return new ExperienceResource(
            $experience->load(['host', 'photos'])
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/v1/experiences/{experience}",
     *     operationId="updateExperience",
     *     summary="Update an experience",
     *     tags={"Experiences"},
     *     security={{ "sessionAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="Experience ID or slug",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateExperienceRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Experience updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceResource")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Experience not found"
     *     )
     * )
     */
    public function update(UpdateExperienceRequest $request, Experience $experience): ExperienceResource
    {
        $experience->update($request->validated());

        if ($experience->is_featured) {
            Cache::forget('featured_experiences');
        }

        return new ExperienceResource($experience);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/v1/experiences/{experience}",
     *     operationId="deleteExperience",
     *     summary="Delete an experience",
     *     tags={"Experiences"},
     *     security={{ "sessionAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="Experience ID or slug",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Experience deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Experience deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Experience not found"
     *     )
     * )
     */
    public function destroy(Experience $experience): JsonResponse
    {
        $this->authorize('delete', $experience);

        if ($experience->is_featured) {
            Cache::forget('featured_experiences');
        }

        $experience->delete();

        return response()->json(['message' => 'Experience deleted successfully']);
    }

    /**
     * Display a listing of experiences by host.
     *
     * @OA\Get(
     *     path="/api/v1/hosts/{hostId}/experiences",
     *     operationId="getHostExperiences",
     *     summary="Get list of experiences by host",
     *     tags={"Experiences"},
     *
     *     @OA\Parameter(
     *         name="hostId",
     *         in="path",
     *         required=true,
     *         description="Host ID",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceCollection")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Host not found"
     *     )
     * )
     */
    public function byHost(Request $request, string $hostId): AnonymousResourceCollection
    {
        $host = User::findOrFail($hostId);

        $queryBuilder = new ExperienceQueryBuilder;
        $queryBuilder->published()
            ->byHost($host->id)
            ->with(['photos'])
            ->sortByRating();

        return ExperienceResource::collection(
            $queryBuilder->getQuery()->paginate($request->per_page ?? 15)
        );
    }

    /**
     * Publish an experience.
     *
     * @OA\Patch(
     *     path="/api/v1/experiences/{experience}/publish",
     *     operationId="publishExperience",
     *     summary="Publish an experience",
     *     tags={"Experiences"},
     *     security={{ "sessionAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="Experience ID or slug",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Experience published successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Experience not found"
     *     )
     * )
     */
    public function publish(Experience $experience): ExperienceResource
    {
        $this->authorize('publish', $experience);

        $experience->status = ExperienceStatus::PUBLISHED;
        $experience->save();

        return new ExperienceResource($experience);
    }

    /**
     * Unpublish an experience.
     *
     * @OA\Patch(
     *     path="/api/v1/experiences/{experience}/unpublish",
     *     operationId="unpublishExperience",
     *     summary="Unpublish an experience",
     *     tags={"Experiences"},
     *     security={{ "sessionAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="Experience ID or slug",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Experience unpublished successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ExperienceResource")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Experience not found"
     *     )
     * )
     */
    public function unpublish(Experience $experience): ExperienceResource
    {
        $this->authorize('unpublish', $experience);

        $experience->status = ExperienceStatus::DRAFT;
        $experience->save();

        return new ExperienceResource($experience);
    }
}
