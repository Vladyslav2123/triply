<?php

namespace App\OpenApi\Schemas\User;

/**
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     title="Запит на оновлення користувача",
 *     description="Структура запиту для оновлення даних користувача",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Ім'я користувача",
 *         example="John",
 *         nullable=true
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
 *         example="john.doe@example.com",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Новий пароль користувача",
 *         example="newPassword123",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Підтвердження нового пароля",
 *         example="newPassword123",
 *         nullable=true
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
 *     ),
 *     @OA\Property(
 *         property="is_banned",
 *         type="boolean",
 *         description="Чи заблокований користувач (тільки для адміністраторів)",
 *         example=false,
 *         nullable=true
 *     )
 * )
 */
class UserUpdateRequestSchema {}
