<?php

namespace App\OpenApi\Schemas\Common;

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     title="Метадані пагінації",
 *     description="Метадані для пагінованих колекцій",
 *
 *     @OA\Property(
 *         property="current_page",
 *         type="integer",
 *         description="Поточна сторінка",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="from",
 *         type="integer",
 *         description="Початковий індекс елементів на поточній сторінці",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="last_page",
 *         type="integer",
 *         description="Номер останньої сторінки",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="path",
 *         type="string",
 *         description="Базовий шлях для пагінації",
 *         example="https://api.triply.blog/api/v1/listings"
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         description="Кількість елементів на сторінці",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="to",
 *         type="integer",
 *         description="Кінцевий індекс елементів на поточній сторінці",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="integer",
 *         description="Загальна кількість елементів",
 *         example=75
 *     )
 * )
 */
class PaginationMetaSchema {}
