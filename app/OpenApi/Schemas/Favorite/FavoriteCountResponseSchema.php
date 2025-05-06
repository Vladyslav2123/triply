<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="FavoriteCountResponse",
 *     title="Відповідь з кількістю обраних",
 *     description="Відповідь з кількістю користувачів, які додали елемент в обране",
 *
 *     @OA\Property(
 *         property="count",
 *         type="integer",
 *         description="Кількість користувачів, які додали елемент в обране",
 *         example=42
 *     )
 * )
 */
class FavoriteCountResponseSchema {}
