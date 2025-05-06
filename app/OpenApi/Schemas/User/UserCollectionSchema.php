<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserCollection",
 *     title="Колекція користувачів",
 *     description="Колекція користувачів з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Масив користувачів",
 *
 *         @OA\Items(ref="#/components/schemas/UserResponse")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Посилання для навігації",
 *         @OA\Property(property="first", type="string", example="http://api.triply.blog/api/v1/users?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.triply.blog/api/v1/users?page=5"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", example="http://api.triply.blog/api/v1/users?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Метадані пагінації",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="path", type="string", example="http://api.triply.blog/api/v1/users"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75)
 *     )
 * )
 */
class UserCollectionSchema {}
