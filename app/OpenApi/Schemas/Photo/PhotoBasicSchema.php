<?php

namespace App\OpenApi\Schemas\Photo;

/**
 * @OA\Schema(
 *     schema="PhotoBasic",
 *     title="Базова інформація про фотографію",
 *     description="Базова інформація про фотографію",
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
 *     )
 * )
 */
class PhotoBasicSchema {}
