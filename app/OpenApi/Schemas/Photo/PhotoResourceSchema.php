<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="PhotoResource",
 *     title="Ресурс фотографії",
 *     description="Ресурс фотографії для API відповідей",
 *
 *     @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVT"),
 *     @OA\Property(property="url", type="string", format="url", example="https://triply.s3.amazonaws.com/photos/image.jpg"),
 *     @OA\Property(property="photoable_type", type="string", example="profile"),
 *     @OA\Property(property="photoable_id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVT"),
 *     @OA\Property(property="disk", type="string", example="s3"),
 *     @OA\Property(property="directory", type="string", example="photos", nullable=true),
 *     @OA\Property(property="size", type="integer", example=1024576, nullable=true),
 *     @OA\Property(property="original_filename", type="string", example="my_photo.jpg", nullable=true),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg", nullable=true),
 *     @OA\Property(property="width", type="integer", example=1920, nullable=true),
 *     @OA\Property(property="height", type="integer", example=1080, nullable=true),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time", example="2023-01-15T14:30:00.000000Z")
 * )
 */
class PhotoResourceSchema {}
