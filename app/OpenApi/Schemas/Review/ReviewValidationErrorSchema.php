<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ReviewValidationError",
 *     title="Помилка валідації відгуку",
 *     description="Відповідь при помилці валідації даних відгуку",
 *
 *     @OA\Property(property="message", type="string", example="Дані не пройшли валідацію"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="content",
 *             type="array",
 *
 *             @OA\Items(type="string", example="Поле 'content' є обов'язковим")
 *         ),
 *
 *         @OA\Property(
 *             property="overall_rating",
 *             type="array",
 *
 *             @OA\Items(type="string", example="Оцінка повинна бути від 1 до 5")
 *         )
 *     )
 * )
 */
class ReviewValidationErrorSchema {}
