<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="FavoriteRemovedResponse",
 *     title="Відповідь при видаленні обраного елементу",
 *     description="Відповідь при успішному видаленні елементу з обраних",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Повідомлення про успішне видалення",
 *         example="Removed from favorites."
 *     )
 * )
 */
class FavoriteRemovedResponseSchema {}
