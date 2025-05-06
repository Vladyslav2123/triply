<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ReviewRequest",
 *     title="Запит на створення відгуку",
 *     description="Дані для створення нового відгуку",
 *     required={"reservation_id", "content", "overall_rating"},
 *
 *     @OA\Property(property="reservation_id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q7"),
 *     @OA\Property(property="content", type="string", example="Чудове місце для відпочинку!"),
 *     @OA\Property(property="overall_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="cleanliness_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="communication_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="check_in_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="accuracy_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="location_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="value_rating", type="number", format="float", minimum=1, maximum=5, example=4)
 * )
 */
class ReviewRequestSchema {}
