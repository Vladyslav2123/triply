<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ReviewUpdateRequest",
 *     title="Запит на оновлення відгуку",
 *     description="Дані для оновлення існуючого відгуку",
 *
 *     @OA\Property(property="content", type="string", example="Оновлений текст відгуку"),
 *     @OA\Property(property="overall_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="cleanliness_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="communication_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="check_in_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="accuracy_rating", type="number", format="float", minimum=1, maximum=5, example=4),
 *     @OA\Property(property="location_rating", type="number", format="float", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="value_rating", type="number", format="float", minimum=1, maximum=5, example=4)
 * )
 */
class ReviewUpdateRequestSchema {}
