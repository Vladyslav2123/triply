<?php

namespace App\OpenApi\Schemas\Favorite;

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     title="Favorite",
 *     description="Модель обраного елементу",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор обраного елементу",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q4"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         format="ulid",
 *         description="ID користувача, який додав елемент до обраних",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q5"
 *     ),
 *     @OA\Property(
 *         property="favoriteable_id",
 *         type="string",
 *         format="ulid",
 *         description="ID об'єкту, який додано до обраних",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q6"
 *     ),
 *     @OA\Property(
 *         property="favoriteable_type",
 *         type="string",
 *         description="Тип об'єкту, який додано до обраних",
 *         example="Listing"
 *     ),
 *     @OA\Property(
 *         property="added_at",
 *         type="string",
 *         format="date",
 *         description="Дата додавання до обраних",
 *         example="2023-12-01"
 *     ),
 *     @OA\Property(
 *         property="favoriteable",
 *         type="object",
 *         description="Об'єкт, який додано до обраних (якщо завантажено)",
 *         oneOf={
 *
 *             @OA\Schema(ref="#/components/schemas/Listing"),
 *             @OA\Schema(ref="#/components/schemas/Experience")
 *         }
 *     )
 * )
 */
class FavoriteSchema {}
