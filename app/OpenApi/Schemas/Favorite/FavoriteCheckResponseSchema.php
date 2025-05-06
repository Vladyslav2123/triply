<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="FavoriteCheckResponse",
 *     title="Відповідь при перевірці обраного елементу",
 *     description="Відповідь при перевірці, чи є елемент в обраних користувача",
 *
 *     @OA\Property(
 *         property="is_favorited",
 *         type="boolean",
 *         description="Чи є елемент в обраних користувача",
 *         example=true
 *     )
 * )
 */
class FavoriteCheckResponseSchema {}
