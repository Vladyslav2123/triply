<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="PhotoUploadRequest",
 *     title="Запит на завантаження фотографії",
 *     description="Запит на завантаження нової фотографії",
 *     required={"file", "photoable_type", "photoable_id"},
 *
 *     @OA\Property(
 *         property="file",
 *         type="string",
 *         format="binary",
 *         description="Файл фотографії (максимум 5MB, тільки зображення)"
 *     ),
 *     @OA\Property(
 *         property="photoable_type",
 *         type="string",
 *         description="Тип об'єкту, до якого належить фотографія",
 *         enum={"profile", "listing", "experience"},
 *         example="profile"
 *     ),
 *     @OA\Property(
 *         property="photoable_id",
 *         type="string",
 *         format="ulid",
 *         description="ID об'єкту, до якого належить фотографія",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="directory",
 *         type="string",
 *         description="Директорія для зберігання фотографії (опціонально)",
 *         example="users",
 *         nullable=true
 *     )
 * )
 */
class PhotoRequestSchema {}

/**
 * @OA\Schema(
 *     schema="ProfilePhotoUploadRequest",
 *     title="Запит на завантаження фотографії профілю",
 *     description="Запит на завантаження нової фотографії для профілю користувача",
 *     required={"photo"},
 *
 *     @OA\Property(
 *         property="photo",
 *         type="string",
 *         format="binary",
 *         description="Файл фотографії профілю (максимум 5MB, тільки зображення)"
 *     )
 * )
 */
class ProfilePhotoUploadRequestSchema {}
