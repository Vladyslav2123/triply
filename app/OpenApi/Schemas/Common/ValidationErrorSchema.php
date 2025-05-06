<?php

namespace App\OpenApi\Schemas\Common;

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     title="Validation Error",
 *     description="Validation error response",
 *
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties={
 *             "type": "array",
 *             "items": {
 *                 "type": "string"
 *             }
 *         },
 *         example={
 *             "title": {"The title field is required."},
 *             "price_per_night": {"The price per night must be at least 1."}
 *         }
 *     )
 * )
 */
class ValidationErrorSchema {}
