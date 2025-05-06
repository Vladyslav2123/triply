<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     title="Ресурс користувача",
 *     description="Ресурс користувача для API відповідей",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор користувача",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="URL-дружній ідентифікатор користувача",
 *         example="john-doe-abc123"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Електронна пошта користувача",
 *         example="john@example.com"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Номер телефону користувача",
 *         example="+380991234567",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *         enum={"user", "admin", "moderator"},
 *         description="Роль користувача в системі",
 *         example="user"
 *     ),
 *     @OA\Property(
 *         property="is_banned",
 *         type="boolean",
 *         description="Чи заблокований користувач",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час створення користувача",
 *         example="2023-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час підтвердження електронної пошти",
 *         example="2023-01-01T00:00:00.000000Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="profile",
 *         description="Профіль користувача",
 *         nullable=true,
 *         ref="#/components/schemas/ProfileResponse"
 *     ),
 *     @OA\Property(
 *         property="photo",
 *         description="Фотографія користувача",
 *         nullable=true,
 *         ref="#/components/schemas/PhotoBasic"
 *     ),
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Повне ім'я користувача",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="listings_count",
 *         type="integer",
 *         description="Кількість оголошень користувача",
 *         example=3,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="experiences_count",
 *         type="integer",
 *         description="Кількість вражень, створених користувачем",
 *         example=2,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="reservations_count",
 *         type="integer",
 *         description="Кількість бронювань користувача",
 *         example=5,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="reviews_count",
 *         type="integer",
 *         description="Кількість відгуків, отриманих користувачем",
 *         example=10,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="favorites_count",
 *         type="integer",
 *         description="Кількість елементів у списку обраного",
 *         example=7,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="average_rating",
 *         type="number",
 *         format="float",
 *         description="Середній рейтинг користувача як господаря",
 *         example=4.8,
 *         nullable=true
 *     )
 * )
 */
class UserResourceSchema {}
