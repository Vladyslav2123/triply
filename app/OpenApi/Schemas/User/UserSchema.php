<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserBasicInfo",
 *     title="Базова інформація користувача",
 *     description="Базова інформація про користувача",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор користувача",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
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
 *         property="slug",
 *         type="string",
 *         description="URL-дружній ідентифікатор користувача",
 *         example="john-doe-abc123"
 *     ),
 *     @OA\Property(
 *         property="is_banned",
 *         type="boolean",
 *         description="Чи заблокований користувач",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Повне ім'я користувача (з профілю)",
 *         example="John Doe"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserAuth",
 *     title="Аутентифікаційні дані користувача",
 *     description="Поля, пов'язані з аутентифікацією користувача",
 *
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час підтвердження електронної пошти",
 *         example="2023-01-01T00:00:00.000000Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="two_factor_confirmed_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час підтвердження двофакторної аутентифікації",
 *         example="2023-01-01T00:00:00.000000Z",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserMetrics",
 *     title="Метрики користувача",
 *     description="Статистичні дані про активність та продуктивність користувача",
 *
 *     @OA\Property(
 *         property="average_rating",
 *         type="number",
 *         format="float",
 *         description="Середній рейтинг користувача як господаря",
 *         example=4.8
 *     ),
 *     @OA\Property(
 *         property="reviews_count",
 *         type="integer",
 *         description="Кількість відгуків, отриманих користувачем як господарем",
 *         example=25
 *     ),
 *     @OA\Property(
 *         property="listings_count",
 *         type="integer",
 *         description="Кількість оголошень користувача",
 *         example=3
 *     ),
 *     @OA\Property(
 *         property="experiences_count",
 *         type="integer",
 *         description="Кількість вражень, створених користувачем",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="reservations_count",
 *         type="integer",
 *         description="Кількість бронювань користувача",
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="favorites_count",
 *         type="integer",
 *         description="Кількість елементів у списку обраного",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="unread_messages_count",
 *         type="integer",
 *         description="Кількість непрочитаних повідомлень",
 *         example=3
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserTimestamps",
 *     title="Часові мітки користувача",
 *     description="Дати створення та оновлення користувача",
 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час створення користувача",
 *         example="2023-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="joined_date",
 *         type="string",
 *         description="Дата приєднання користувача у форматі 'Місяць Рік'",
 *         example="Січень 2023"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     title="Користувач",
 *     description="Модель користувача з усіма зв'язками",
 *
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/UserBasicInfo"),
 *         @OA\Schema(ref="#/components/schemas/UserAuth"),
 *         @OA\Schema(ref="#/components/schemas/UserMetrics"),
 *         @OA\Schema(ref="#/components/schemas/UserTimestamps"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="photo",
 *                 description="Фотографія користувача",
 *                 nullable=true,
 *                 ref="#/components/schemas/PhotoBasic"
 *             )
 *         ),
 *
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="profile",
 *                 description="Профіль користувача з детальною інформацією",
 *                 nullable=true,
 *                 ref="#/components/schemas/Profile"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UserWithProfile",
 *     title="Користувач з профілем",
 *     description="Розширена інформація про користувача з даними профілю",
 *
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/UserBasicInfo"),
 *         @OA\Schema(ref="#/components/schemas/UserMetrics"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="photo",
 *                 description="Фотографія користувача",
 *                 nullable=true,
 *                 ref="#/components/schemas/PhotoBasic"
 *             ),
 *             @OA\Property(
 *                 property="profile",
 *                 type="object",
 *                 description="Дані профілю користувача",
 *                 properties={
 *                     @OA\Property(property="is_superhost", type="boolean", example=false, description="Чи є користувач суперхостом"),
 *                     @OA\Property(property="response_speed", type="number", format="float", example=98.5, description="Швидкість відповіді у відсотках"),
 *                     @OA\Property(property="gender", type="string", enum={"male", "female", "non-binary", "prefer not to say"}, nullable=true, description="Стать користувача"),
 *                     @OA\Property(property="work", type="string", nullable=true, description="Місце роботи"),
 *                     @OA\Property(property="job_title", type="string", nullable=true, description="Посада"),
 *                     @OA\Property(property="company", type="string", nullable=true, description="Компанія"),
 *                     @OA\Property(property="school", type="string", nullable=true, description="Навчальний заклад"),
 *                     @OA\Property(property="education_level", type="string", enum={"high_school", "bachelor", "master", "phd"}, nullable=true, description="Рівень освіти"),
 *                     @OA\Property(property="languages", type="array", @OA\Items(type="string"), description="Мови, якими володіє користувач"),
 *                     @OA\Property(property="interests", type="array", @OA\Items(type="string"), description="Інтереси користувача"),
 *                     @OA\Property(property="about", type="string", nullable=true, description="Інформація про користувача"),
 *                     @OA\Property(property="location", type="object", nullable=true, description="Місцезнаходження користувача",
 *                         properties={
 *                             @OA\Property(property="country", type="string", example="Україна"),
 *                             @OA\Property(property="city", type="string", example="Київ"),
 *                             @OA\Property(property="address", type="string", example="вул. Хрещатик, 1")
 *                         }
 *                     ),
 *                     @OA\Property(property="social_links", type="object", description="Посилання на соціальні мережі",
 *                         properties={
 *                             @OA\Property(property="facebook", type="string", nullable=true),
 *                             @OA\Property(property="instagram", type="string", nullable=true),
 *                             @OA\Property(property="twitter", type="string", nullable=true),
 *                             @OA\Property(property="linkedin", type="string", nullable=true)
 *                         }
 *                     )
 *                 }
 *             ),
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
 *                 property="joined_date",
 *                 type="string",
 *                 description="Дата приєднання користувача у форматі 'Місяць Рік'",
 *                 example="Січень 2023"
 *             )
 *         )
 *     }
 * )
 */
class UserSchema {}
