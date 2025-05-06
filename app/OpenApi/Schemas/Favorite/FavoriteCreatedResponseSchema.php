<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="FavoriteCreatedResponse",
 *     title="Відповідь при створенні обраного елементу",
 *     description="Відповідь при успішному додаванні елементу до обраних",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Повідомлення про успішне додавання",
 *         example="Added to favorites."
 *     ),
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/Favorite"
 *     )
 * )
 */
class FavoriteCreatedResponseSchema {}
