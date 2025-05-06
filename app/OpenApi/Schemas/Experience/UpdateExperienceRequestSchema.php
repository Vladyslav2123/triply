<?php

namespace App\OpenApi\Schemas\Experience;

/**
 * @OA\Schema(
 *     schema="UpdateExperienceRequest",
 *     title="Запит на оновлення враження",
 *     description="Дані для оновлення існуючого враження",
 *
 *     @OA\Property(property="title", type="string", example="Оновлений кулінарний майстер-клас у Києві"),
 *     @OA\Property(property="description", type="string", example="Оновлений опис: навчіться готувати традиційні українські страви"),
 *     @OA\Property(property="category", type="string", example="food_and_drink"),
 *     @OA\Property(property="sub_category", type="string", example="cooking_class"),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         @OA\Property(property="country", type="string", example="Україна"),
 *         @OA\Property(property="city", type="string", example="Київ"),
 *         @OA\Property(property="state", type="string", example="Київська область"),
 *         @OA\Property(property="street", type="string", example="вул. Хрещатик, 1"),
 *         @OA\Property(
 *             property="coordinates",
 *             type="object",
 *             @OA\Property(property="latitude", type="number", format="float", example=50.4501),
 *             @OA\Property(property="longitude", type="number", format="float", example=30.5234)
 *         )
 *     ),
 *     @OA\Property(
 *         property="languages",
 *         type="array",
 *
 *         @OA\Items(type="string", example="uk")
 *     ),
 *
 *     @OA\Property(
 *         property="pricing",
 *         type="object",
 *         @OA\Property(property="base_price", type="number", format="float", example=600),
 *         @OA\Property(property="currency", type="string", example="UAH")
 *     ),
 *     @OA\Property(
 *         property="grouping",
 *         type="object",
 *         @OA\Property(property="min_guests", type="integer", example=2),
 *         @OA\Property(property="max_guests", type="integer", example=12)
 *     ),
 *     @OA\Property(property="duration", type="string", format="date-time", example="2023-12-01T15:00:00Z"),
 *     @OA\Property(property="location_type", type="string", example="indoor"),
 *     @OA\Property(property="location_note", type="string", example="Оновлена примітка: зустріч біля входу в будівлю")
 * )
 */
class UpdateExperienceRequestSchema {}
