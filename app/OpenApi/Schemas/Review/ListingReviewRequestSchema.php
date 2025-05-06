<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ListingReviewRequest",
 *     title="Запит на створення відгуку для оголошення",
 *     description="Дані для створення нового відгуку для оголошення",
 *     required={"content", "cleanliness_rating", "communication_rating", "check_in_rating", "accuracy_rating", "location_rating", "value_rating"},
 *
 *     @OA\Property(property="content", type="string", example="Чудове місце для відпочинку!"),
 *     @OA\Property(property="cleanliness_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="communication_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="check_in_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="accuracy_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="location_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="value_rating", type="number", format="float", minimum=1, maximum=5, example=4)
 * )
 */
class ListingReviewRequestSchema {}
