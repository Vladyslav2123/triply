<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\Schema(
 *     schema="ProfileAuthExample",
 *     title="Приклад використання автентифікації для профілю",
 *     description="Цей файл є прикладом, як використовувати автентифікацію в документації OpenAPI"
 * )
 */
class ProfileAuthExample {}

/**
 * @OA\Get(
 *     path="/api/v1/profile",
 *     summary="Отримати профіль поточного користувача",
 *     description="Повертає детальну інформацію про профіль автентифікованого користувача",
 *     tags={"Profile"},
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Успішна операція",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизований доступ",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 */

/**
 * @OA\Put(
 *     path="/api/v1/profile",
 *     summary="Оновити профіль користувача",
 *     description="Оновлює інформацію профілю автентифікованого користувача",
 *     tags={"Profile"},
 *     security={{"sessionAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Профіль успішно оновлено",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизований доступ",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Помилка валідації",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/api/v1/profiles/{id}",
 *     summary="Отримати профіль за ID",
 *     description="Повертає детальну інформацію про профіль за вказаним ID. Не потребує автентифікації.",
 *     tags={"Profile"},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID профілю",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Успішна операція",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Профіль не знайдено",
 *
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 */
