<?php

namespace App\OpenApi\Schemas\Experience;

/**
 * @OA\Schema(
 *     schema="ExperienceCollection",
 *     title="Колекція вражень",
 *     description="Колекція вражень з пагінацією",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Масив об'єктів вражень",
 *
 *         @OA\Items(ref="#/components/schemas/Experience")
 *     ),
 *
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Посилання для навігації по сторінках",
 *         @OA\Property(property="first", type="string", format="url", description="Посилання на першу сторінку", example="https://api.triply.blog/api/v1/experiences?page=1"),
 *         @OA\Property(property="last", type="string", format="url", description="Посилання на останню сторінку", example="https://api.triply.blog/api/v1/experiences?page=5"),
 *         @OA\Property(property="prev", type="string", format="url", description="Посилання на попередню сторінку", nullable=true),
 *         @OA\Property(property="next", type="string", format="url", description="Посилання на наступну сторінку", example="https://api.triply.blog/api/v1/experiences?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Метадані пагінації та фільтрації",
 *         @OA\Property(property="current_page", type="integer", description="Поточна сторінка", example=1),
 *         @OA\Property(property="from", type="integer", description="Початковий індекс елементів на поточній сторінці", example=1),
 *         @OA\Property(property="last_page", type="integer", description="Номер останньої сторінки", example=5),
 *         @OA\Property(property="path", type="string", description="Базовий URL для пагінації", example="https://api.triply.blog/api/v1/experiences"),
 *         @OA\Property(property="per_page", type="integer", description="Кількість елементів на сторінці", example=15),
 *         @OA\Property(property="to", type="integer", description="Кінцевий індекс елементів на поточній сторінці", example=15),
 *         @OA\Property(property="total", type="integer", description="Загальна кількість елементів", example=75),
 *         @OA\Property(
 *             property="filters",
 *             type="object",
 *             description="Застосовані фільтри",
 *             @OA\Property(property="category", type="string", description="Фільтр за категорією", example="food_and_drink"),
 *             @OA\Property(property="city", type="string", description="Фільтр за містом", example="Київ"),
 *             @OA\Property(property="price_min", type="integer", description="Мінімальна ціна", example=100),
 *             @OA\Property(property="price_max", type="integer", description="Максимальна ціна", example=1000)
 *         )
 *     )
 * )
 */
class ExperienceCollectionSchema {}
