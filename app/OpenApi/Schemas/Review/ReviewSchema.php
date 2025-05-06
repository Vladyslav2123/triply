<?php

namespace App\OpenApi\Schemas\Review;

/**
 * @OA\Schema(
 *     schema="Review",
 *     title="Review",
 *     description="Модель відгуку",
 *
 *     @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q4"),
 *     @OA\Property(property="content", type="string", example="Чудове місце для відпочинку!"),
 *     @OA\Property(property="overall_rating", type="number", format="float", minimum=1, maximum=5, example=4.5),
 *     @OA\Property(property="cleanliness_rating", type="number", format="float", minimum=1, maximum=5, example=5, nullable=true),
 *     @OA\Property(property="communication_rating", type="number", format="float", minimum=1, maximum=5, example=4, nullable=true),
 *     @OA\Property(property="check_in_rating", type="number", format="float", minimum=1, maximum=5, example=4.5, nullable=true),
 *     @OA\Property(property="accuracy_rating", type="number", format="float", minimum=1, maximum=5, example=4, nullable=true),
 *     @OA\Property(property="location_rating", type="number", format="float", minimum=1, maximum=5, example=5, nullable=true),
 *     @OA\Property(property="value_rating", type="number", format="float", minimum=1, maximum=5, example=4, nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-02T12:00:00Z"),
 *     @OA\Property(
 *         property="reviewer",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q5"),
 *         @OA\Property(property="name", type="string", example="Іван Петренко"),
 *         @OA\Property(property="profile_photo", type="string", nullable=true, example="https://example.com/photos/user.jpg")
 *     ),
 *     @OA\Property(
 *         property="reviewable",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q6"),
 *         @OA\Property(property="type", type="string", enum={"listing", "experience"}, example="listing"),
 *         @OA\Property(property="title", type="string", example="Затишна квартира в центрі міста"),
 *         @OA\Property(property="slug", type="string", example="zatyshna-kvartyra-v-tsentri-mista")
 *     ),
 *     @OA\Property(
 *         property="reservation",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q7"),
 *         @OA\Property(property="check_in", type="string", format="date", example="2023-01-01"),
 *         @OA\Property(property="check_out", type="string", format="date", example="2023-01-05")
 *     )
 * )
 */
class ReviewSchema {}
