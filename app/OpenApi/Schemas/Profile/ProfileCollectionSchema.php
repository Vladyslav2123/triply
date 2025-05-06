<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\Schema(
 *     schema="ProfileCollection",
 *     title="Колекція профілів",
 *     description="Колекція профілів користувачів з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Масив профілів користувачів",
 *
 *         @OA\Items(ref="#/components/schemas/ProfileResponse")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Посилання для навігації",
 *         @OA\Property(property="first", type="string", example="http://api.triply.blog/api/v1/profiles?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.triply.blog/api/v1/profiles?page=5"),
 *         @OA\Property(property="prev", type="string", example="http://api.triply.blog/api/v1/profiles?page=1", nullable=true),
 *         @OA\Property(property="next", type="string", example="http://api.triply.blog/api/v1/profiles?page=3", nullable=true),
 *         @OA\Property(property="self", type="string", example="http://api.triply.blog/api/v1/profiles?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Метадані пагінації",
 *         @OA\Property(property="current_page", type="integer", example=2),
 *         @OA\Property(property="from", type="integer", example=16),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="path", type="string", example="http://api.triply.blog/api/v1/profiles"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=30),
 *         @OA\Property(property="total", type="integer", example=75)
 *     )
 * )
 */
class ProfileCollectionSchema {}
