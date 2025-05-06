<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="Photo",
 *     title="Фотографія",
 *     description="Модель фотографії",
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
 *         description="URL фотографії для відображення",
 *         example="https://triply.s3.amazonaws.com/photos/image.jpg"
 *     ),
 *     @OA\Property(
 *         property="full_url",
 *         type="string",
 *         format="url",
 *         description="Повний URL фотографії з урахуванням диску зберігання",
 *         example="https://triply.s3.amazonaws.com/photos/image.jpg"
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
 *         property="uploaded_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час завантаження фотографії",
 *         example="2023-01-15T14:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час створення запису",
 *         example="2023-01-15T14:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час останнього оновлення запису",
 *         example="2023-01-15T14:30:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата та час видалення запису (soft delete)",
 *         example="2023-01-15T14:30:00.000000Z",
 *         nullable=true
 *     )
 * )
 */
class PhotoSchema {}
