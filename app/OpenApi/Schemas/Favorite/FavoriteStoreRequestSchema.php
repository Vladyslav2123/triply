<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="FavoriteStoreRequest",
 *     title="Запит на додавання елементу до обраних",
 *     description="Запит на додавання елементу до обраних користувача",
 *     required={"favoriteable_type", "favoriteable_id"},
 *
 *     @OA\Property(
 *         property="favoriteable_type",
 *         type="string",
 *         description="Тип об'єкту (listing або experience)",
 *         example="listing"
 *     ),
 *     @OA\Property(
 *         property="favoriteable_id",
 *         type="string",
 *         format="ulid",
 *         description="ID об'єкту",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q4"
 *     )
 * )
 */
class FavoriteStoreRequestSchema {}
