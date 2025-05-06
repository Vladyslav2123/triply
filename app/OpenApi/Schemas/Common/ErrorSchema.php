<?php

namespace App\OpenApi\Schemas\Common;

/**
 * @OA\Schema(
 *     schema="Error",
 *     title="Помилка",
 *     description="Стандартна структура помилки API",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Повідомлення про помилку",
 *         example="Ресурс не знайдено"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Детальна інформація про помилки валідації"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         description="Код помилки HTTP",
 *         example=404
 *     ),
 *     @OA\Property(
 *         property="debug",
 *         type="object",
 *         description="Додаткова інформація для налагодження (тільки в режимі розробки)",
 *         @OA\Property(property="file", type="string", example="ListingController.php"),
 *         @OA\Property(property="line", type="integer", example=125),
 *         @OA\Property(property="trace", type="array", @OA\Items(type="string"))
 *     )
 * )
 */
class ErrorSchema {}
