<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="PhotoCollection",
 *     title="Колекція фотографій",
 *     description="Колекція фотографій з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Масив фотографій",
 *
 *         @OA\Items(ref="#/components/schemas/Photo")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Посилання для навігації",
 *         @OA\Property(property="first", type="string", format="url", example="https://api.triply.test/api/v1/photos?page=1"),
 *         @OA\Property(property="last", type="string", format="url", example="https://api.triply.test/api/v1/photos?page=5"),
 *         @OA\Property(property="prev", type="string", format="url", nullable=true, example=null),
 *         @OA\Property(property="next", type="string", format="url", nullable=true, example="https://api.triply.test/api/v1/photos?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Метадані пагінації",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="links", type="array",
 *
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="url", type="string", format="url", nullable=true, example="https://api.triply.test/api/v1/photos?page=1"),
 *                 @OA\Property(property="label", type="string", example="1"),
 *                 @OA\Property(property="active", type="boolean", example=true)
 *             )
 *         ),
 *         @OA\Property(property="path", type="string", format="url", example="https://api.triply.test/api/v1/photos"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75)
 *     )
 * )
 */
class PhotoCollectionSchema {}
