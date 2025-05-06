<?php

namespace App\OpenApi\Schemas\Listing;

/**
 * @OA\Schema(
 *     schema="ListingCollection",
 *     title="Колекція оголошень",
 *     description="Колекція оголошень з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Масив оголошень",
 *
 *         @OA\Items(ref="#/components/schemas/Listing")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Посилання для навігації",
 *         @OA\Property(
 *             property="first",
 *             type="string",
 *             format="url",
 *             description="Посилання на першу сторінку",
 *             example="https://api.triply.blog/api/v1/listings?page=1"
 *         ),
 *         @OA\Property(
 *             property="last",
 *             type="string",
 *             format="url",
 *             description="Посилання на останню сторінку",
 *             example="https://api.triply.blog/api/v1/listings?page=5"
 *         ),
 *         @OA\Property(
 *             property="prev",
 *             type="string",
 *             format="url",
 *             description="Посилання на попередню сторінку",
 *             nullable=true
 *         ),
 *         @OA\Property(
 *             property="next",
 *             type="string",
 *             format="url",
 *             description="Посилання на наступну сторінку",
 *             example="https://api.triply.blog/api/v1/listings?page=2"
 *         )
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         description="Метадані пагінації",
 *         allOf={
 *
 *             @OA\Schema(ref="#/components/schemas/PaginationMeta")
 *         },
 *
 *         @OA\Property(
 *             property="filters",
 *             type="object",
 *             description="Застосовані фільтри",
 *             example={"price_min": 500, "price_max": 2000, "type": "apartment"}
 *         )
 *     )
 * )
 */
class ListingCollectionSchema {}
