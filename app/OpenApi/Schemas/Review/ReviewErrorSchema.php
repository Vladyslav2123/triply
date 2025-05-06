<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="ReviewError",
 *     title="Помилка відгуку",
 *     description="Відповідь при виникненні помилки з відгуком",
 *
 *     @OA\Property(property="message", type="string", example="Не вдалося створити відгук"),
 *     @OA\Property(property="status", type="integer", example=500)
 * )
 */
class ReviewErrorSchema {}
