<?php

namespace App\OpenApi\Schemas\Common;

/**
 * @OA\Schema(
 *     schema="Timestamps",
 *     title="Часові мітки",
 *     description="Стандартні часові мітки Laravel",
 *
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата створення запису",
 *         example="2023-01-15T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата останнього оновлення запису",
 *         example="2023-02-20T15:30:00Z"
 *     )
 * )
 */
class TimestampsSchema {}
