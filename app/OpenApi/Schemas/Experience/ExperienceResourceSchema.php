<?php

namespace App\OpenApi\Schemas\Experience;

/**
 * @OA\Schema(
 *     schema="ExperienceResource",
 *     title="Experience Resource",
 *     description="Experience resource",
 *
 *     @OA\Property(property="id", type="string", format="ulid", example="01HGXVZ6QJVT5MMMYJ9XQVGZJ4"),
 *     @OA\Property(property="slug", type="string", example="cooking-class-in-kyiv"),
 *     @OA\Property(property="title", type="string", example="Cooking class in Kyiv"),
 *     @OA\Property(property="description", type="string", example="Learn to cook traditional Ukrainian dishes"),
 *     @OA\Property(property="host_id", type="string", format="ulid", example="01HGXVZ6QJVT5MMMYJ9XQVGZJ4"),
 *     @OA\Property(property="category", type="string", example="food_and_drink"),
 *     @OA\Property(property="sub_category", type="string", example="cooking_class"),
 *     @OA\Property(property="location", type="object",
 *         @OA\Property(property="country", type="string", example="Ukraine"),
 *         @OA\Property(property="city", type="string", example="Kyiv"),
 *         @OA\Property(property="state", type="string", example="Kyiv Oblast"),
 *         @OA\Property(property="coordinates", type="object",
 *             @OA\Property(property="latitude", type="number", format="float", example=50.4501),
 *             @OA\Property(property="longitude", type="number", format="float", example=30.5234)
 *         )
 *     ),
 *     @OA\Property(property="languages", type="array", @OA\Items(type="string", example="en")),
 *     @OA\Property(property="status", type="string", example="published"),
 *     @OA\Property(property="host", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/PhotoResource"))
 * )
 */
class ExperienceResourceSchema {}
