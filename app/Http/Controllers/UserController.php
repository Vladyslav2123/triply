<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get list of users (admin only)",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"user", "host", "admin", "guest"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search users by email or phone",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResponse")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not an admin",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);
        $query = User::query();

        if ($request->has('role')) {
            $role = $request->input('role');
            $query->where('role', $role);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     *
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user details",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserWithProfile")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not authorized to view this user",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'data' => new UserResource($user->load('profile')),
        ]);
    }

    /**
     * Check if a user exists by email or phone.
     *
     * This endpoint allows checking if a user with the provided email or phone number already exists in the system.
     * It can be used during registration or account creation to validate if the email or phone is already taken.
     * At least one of email or phone must be provided.
     *
     * @OA\Post(
     *     path="/api/v1/users/exists",
     *     operationId="checkUserExists",
     *     summary="Check if a user exists by email or phone",
     *     description="Checks if a user with the provided email or phone number exists in the database",
     *     tags={"Users"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials to check",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 nullable=true,
     *                 description="Email address to check",
     *                 example="user@example.com"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 nullable=true,
     *                 description="Phone number to check (international format)",
     *                 example="+380123456789"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User existence check result",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="exists",
     *                 type="boolean",
     *                 example=true,
     *                 description="True if a user with the provided email or phone exists, false otherwise"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required when phone is not present.")),
     *                 @OA\Property(property="phone", type="array", @OA\Items(type="string", example="The phone field is required when email is not present."))
     *             )
     *         )
     *     )
     * )
     */
    public function exists(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required_without:phone|nullable|email',
            'phone' => 'required_without:email|nullable|string',
        ]);

        $query = User::query();

        if (isset($validated['email']) && isset($validated['phone'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('email', $validated['email'])
                    ->orWhere('phone', $validated['phone']);
            });
        } elseif (isset($validated['email'])) {
            $query->where('email', $validated['email']);
        } elseif (isset($validated['phone'])) {
            $query->where('phone', $validated['phone']);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }
}
