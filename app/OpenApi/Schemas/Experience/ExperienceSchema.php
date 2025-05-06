<?php

namespace App\OpenApi\Schemas\Experience;

/**
 * @OA\Schema(
 *     schema="Experience",
 *     title="Враження",
 *     description="Модель враження",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор враження",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q4"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="URL-дружнє представлення назви враження",
 *         example="cooking-class-in-kyiv"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Назва враження",
 *         example="Кулінарний майстер-клас у Києві"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Детальний опис враження",
 *         example="Навчіться готувати традиційні українські страви"
 *     ),
 *     @OA\Property(
 *         property="host_id",
 *         type="string",
 *         format="ulid",
 *         description="ID користувача, який є організатором враження",
 *         example="01HJD7NWGN7XXZSY2SJRJVT1Q5"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         description="Категорія враження",
 *         example="food_and_drink"
 *     ),
 *     @OA\Property(
 *         property="sub_category",
 *         type="string",
 *         description="Підкатегорія враження",
 *         example="cooking_class"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         description="Місце проведення враження",
 *         @OA\Property(property="country", type="string", description="Країна", example="Україна"),
 *         @OA\Property(property="city", type="string", description="Місто", example="Київ"),
 *         @OA\Property(property="state", type="string", description="Область/штат", example="Київська область"),
 *         @OA\Property(
 *             property="coordinates",
 *             type="object",
 *             description="Географічні координати",
 *             @OA\Property(property="latitude", type="number", format="float", description="Широта", example=50.4501),
 *             @OA\Property(property="longitude", type="number", format="float", description="Довгота", example=30.5234)
 *         )
 *     ),
 *     @OA\Property(
 *         property="languages",
 *         type="array",
 *         description="Мови, якими проводиться враження",
 *
 *         @OA\Items(type="string", example="uk")
 *     ),
 *
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Статус враження (опубліковано, чернетка, тощо)",
 *         example="published"
 *     ),
 *     @OA\Property(
 *         property="is_featured",
 *         type="boolean",
 *         description="Чи є враження рекомендованим",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="views_count",
 *         type="integer",
 *         description="Кількість переглядів враження",
 *         example=1250
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="number",
 *         format="float",
 *         description="Середній рейтинг враження",
 *         example=4.8
 *     ),
 *     @OA\Property(
 *         property="reviews_count",
 *         type="integer",
 *         description="Кількість відгуків про враження",
 *         example=42
 *     ),
 *     @OA\Property(
 *         property="favorites_count",
 *         type="integer",
 *         description="Кількість користувачів, які додали враження в обране",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="is_favorited",
 *         type="boolean",
 *         description="Чи додано враження в обране поточним користувачем",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="pricing",
 *         type="object",
 *         description="Інформація про ціни",
 *         @OA\Property(property="base_price", type="number", format="float", description="Базова ціна", example=500),
 *         @OA\Property(property="currency", type="string", description="Валюта", example="UAH")
 *     ),
 *     @OA\Property(
 *         property="host",
 *         type="object",
 *         description="Інформація про організатора враження",
 *         @OA\Property(property="id", type="string", format="ulid", description="ID організатора", example="01HJD7NWGN7XXZSY2SJRJVT1Q5"),
 *         @OA\Property(property="name", type="string", description="Ім'я організатора", example="Олена Петренко"),
 *         @OA\Property(property="profile_photo", type="string", nullable=true, description="URL фото профілю", example="https://example.com/photos/user.jpg")
 *     ),
 *     @OA\Property(
 *         property="photos",
 *         type="array",
 *         description="Фотографії враження",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(property="id", type="string", format="ulid", description="ID фото", example="01HJD7NWGN7XXZSY2SJRJVT1Q6"),
 *             @OA\Property(property="url", type="string", description="URL фото", example="https://example.com/photos/experience.jpg"),
 *             @OA\Property(property="is_primary", type="boolean", description="Чи є фото головним", example=true)
 *         )
 *     )
 * )
 */
class ExperienceSchema {}
