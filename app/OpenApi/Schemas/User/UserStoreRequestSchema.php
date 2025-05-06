<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserStoreRequest",
 *     title="Запит на створення користувача",
 *     description="Структура запиту для створення нового користувача",
 *     required={"name", "email", "password", "password_confirmation"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Ім'я користувача",
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="surname",
 *         type="string",
 *         description="Прізвище користувача",
 *         example="Doe",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Електронна пошта користувача (унікальна)",
 *         example="john.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Пароль користувача",
 *         example="password123"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Підтвердження пароля",
 *         example="password123"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Номер телефону користувача (унікальний)",
 *         example="+380991234567",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *         enum={"user", "admin", "moderator"},
 *         description="Роль користувача в системі (тільки для адміністраторів)",
 *         example="user",
 *         nullable=true
 *     )
 * )
 */
class UserStoreRequestSchema {}
