<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExperienceAvailabilityRequest;
use App\Http\Requests\UpdateExperienceAvailabilityRequest;
use App\Http\Resources\ExperienceAvailabilityResource;
use App\Models\Experience;
use App\Models\ExperienceAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Experience $experience): AnonymousResourceCollection
    {
        $query = $experience->availability();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return ExperienceAvailabilityResource::collection(
            $query->orderBy('date')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExperienceAvailabilityRequest $request, Experience $experience): ExperienceAvailabilityResource
    {
        $this->authorize('update', $experience);

        $availability = new ExperienceAvailability($request->validated());
        $availability->experience_id = $experience->id;
        $availability->save();

        return new ExperienceAvailabilityResource($availability);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExperienceAvailability $experienceAvailability)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExperienceAvailabilityRequest $request, ExperienceAvailability $experienceAvailability)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExperienceAvailability $experienceAvailability)
    {
        //
    }

    /**
     * Check availability for a specific date and number of guests.
     */
    public function check(Request $request, Experience $experience): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'guests' => 'required|integer|min:1',
        ]);

        $date = $request->date;
        $guestsCount = $request->guests;

        $availability = $experience->availability()
            ->where('date', $date)
            ->where('is_available', true)
            ->first();

        if (! $availability) {
            return response()->json([
                'is_available' => false,
                'available_slots' => 0,
                'message' => 'No availability for this date',
            ]);
        }

        $isAvailable = $availability->slots >= $guestsCount;

        return response()->json([
            'is_available' => $isAvailable,
            'available_slots' => $availability->slots,
            'message' => $isAvailable
                ? 'Experience is available for booking'
                : 'Not enough slots available for the requested number of guests',
        ]);
    }
}
