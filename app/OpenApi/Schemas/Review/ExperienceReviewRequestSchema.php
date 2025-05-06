<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ExperienceReviewRequest",
 *     title="Запит на створення відгуку для враження",
 *     description="Дані для створення нового відгуку для враження",
 *     required={"content", "overall_rating"},
 *
 *     @OA\Property(property="content", type="string", example="Неймовірне враження! Рекомендую всім."),
 *     @OA\Property(property="overall_rating", type="number", format="float", minimum=1, maximum=5, example=4.5)
 * )
 */
class ExperienceReviewRequestSchema {}
