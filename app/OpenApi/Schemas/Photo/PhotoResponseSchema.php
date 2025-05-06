<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="PhotoResponse",
 *     title="Відповідь з даними фотографії",
 *     description="Структура відповіді з даними фотографії",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор фотографії",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         format="url",
 *         description="Повний URL фотографії для відображення",
 *         example="https://triply.s3.amazonaws.com/photos/image.jpg"
 *     ),
 *     @OA\Property(
 *         property="photoable_type",
 *         type="string",
 *         description="Тип об'єкту, до якого належить фотографія",
 *         example="profile",
 *         enum={"profile", "listing", "experience"}
 *     ),
 *     @OA\Property(
 *         property="photoable_id",
 *         type="string",
 *         format="ulid",
 *         description="ID об'єкту, до якого належить фотографія",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="disk",
 *         type="string",
 *         description="Диск зберігання фотографії",
 *         example="s3"
 *     ),
 *     @OA\Property(
 *         property="directory",
 *         type="string",
 *         description="Директорія зберігання фотографії",
 *         example="users",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="size",
 *         type="integer",
 *         description="Розмір фотографії в байтах",
 *         example=1024576,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="original_filename",
 *         type="string",
 *         description="Оригінальна назва файлу",
 *         example="my_photo.jpg",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="mime_type",
 *         type="string",
 *         description="MIME-тип фотографії",
 *         example="image/jpeg",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="width",
 *         type="integer",
 *         description="Ширина фотографії в пікселях",
 *         example=1920,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="height",
 *         type="integer",
 *         description="Висота фотографії в пікселях",
 *         example=1080,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="uploaded_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час завантаження фотографії",
 *         example="2023-01-15T14:30:00.000000Z"
 *     )
 * )
 */
class PhotoResponseSchema {}

/**
 * @OA\Schema(
 *     schema="ProfilePhotoResponse",
 *     title="Відповідь після завантаження фотографії профілю",
 *     description="Структура відповіді після успішного завантаження фотографії профілю",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Повідомлення про успішне завантаження",
 *         example="Фото профілю успішно завантажено"
 *     ),
 *     @OA\Property(
 *         property="photo",
 *         ref="#/components/schemas/PhotoResponse",
 *         description="Дані завантаженої фотографії"
 *     )
 * )
 */
class ProfilePhotoResponseSchema {}

/**
 * @OA\Schema(
 *     schema="PhotoDeleteResponse",
 *     title="Відповідь після видалення фотографії",
 *     description="Структура відповіді після успішного видалення фотографії",
 *
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Повідомлення про успішне видалення",
 *         example="Фото успішно видалено"
 *     )
 * )
 */
class PhotoDeleteResponseSchema {}
