<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserResponse",
 *     title="Відповідь з даними користувача",
 *     description="Структура відповіді API з даними користувача",
 *
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/UserBasicInfo"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="email_verified_at",
 *                 type="string",
 *                 format="date-time",
 *                 description="Дата та час підтвердження електронної пошти",
 *                 example="2023-01-01T00:00:00.000000Z",
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="created_at",
 *                 type="string",
 *                 format="date-time",
 *                 description="Дата та час створення користувача",
 *                 example="2023-01-01T00:00:00.000000Z"
 *             ),
 *             @OA\Property(
 *                 property="photo",
 *                 description="Фотографія користувача",
 *                 nullable=true,
 *                 ref="#/components/schemas/PhotoBasic"
 *             ),
 *             @OA\Property(
 *                 property="profile",
 *                 description="Профіль користувача",
 *                 nullable=true,
 *                 ref="#/components/schemas/ProfileResponse"
 *             ),
 *             @OA\Property(
 *                 property="listings_count",
 *                 type="integer",
 *                 description="Кількість оголошень користувача",
 *                 example=3,
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="experiences_count",
 *                 type="integer",
 *                 description="Кількість вражень, створених користувачем",
 *                 example=2,
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="reservations_count",
 *                 type="integer",
 *                 description="Кількість бронювань користувача",
 *                 example=5,
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="reviews_count",
 *                 type="integer",
 *                 description="Кількість відгуків, отриманих користувачем",
 *                 example=10,
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="favorites_count",
 *                 type="integer",
 *                 description="Кількість елементів у списку обраного",
 *                 example=7,
 *                 nullable=true
 *             ),
 *             @OA\Property(
 *                 property="average_rating",
 *                 type="number",
 *                 format="float",
 *                 description="Середній рейтинг користувача як господаря",
 *                 example=4.8,
 *                 nullable=true
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserDetailResponse",
 *     title="Детальна відповідь з даними користувача",
 *     description="Розширена структура відповіді API з детальними даними користувача",
 *
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/UserResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="is_verified",
 *                 type="boolean",
 *                 description="Чи верифікований користувач",
 *                 example=true
 *             ),
 *             @OA\Property(
 *                 property="joined_date",
 *                 type="string",
 *                 description="Дата приєднання користувача у форматі 'Місяць Рік'",
 *                 example="Січень 2023"
 *             ),
 *             @OA\Property(
 *                 property="active_listings",
 *                 type="array",
 *                 description="Активні оголошення користувача",
 *
 *                 @OA\Items(ref="#/components/schemas/Listing"),
 *                 nullable=true
 *             ),
 *
 *             @OA\Property(
 *                 property="active_experiences",
 *                 type="array",
 *                 description="Активні враження користувача",
 *
 *                 @OA\Items(ref="#/components/schemas/Experience"),
 *                 nullable=true
 *             )
 *         )
 *     }
 * )
 */
class UserResponseSchema {}
