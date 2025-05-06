<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ReviewCollection",
 *     title="Колекція відгуків",
 *     description="Колекція відгуків з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Review")
 *     ),
 *
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="path", type="string", example="http://api.triply.blog/api/v1/reviews"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75)
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://api.triply.blog/api/v1/reviews?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.triply.blog/api/v1/reviews?page=5"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", example="http://api.triply.blog/api/v1/reviews?page=2")
 *     )
 * )
 */
class ReviewCollectionSchema {}
