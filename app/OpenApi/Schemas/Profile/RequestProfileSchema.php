<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\RequestBody(
 *     request="UpdateProfileRequest",
 *     description="Profile data for update",
 *     required=true,
 *
 *     @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
 * )
 *
 * @OA\Schema(
 *     schema="RequestProfile",
 *     title="Profile Request",
 *     description="Profile request data for updating a profile",
 *
 *     @OA\Property(property="first_name", type="string", example="John", nullable=true),
 *     @OA\Property(property="last_name", type="string", example="Doe", nullable=true),
 *     @OA\Property(property="phone", type="string", example="+380991234567", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01", nullable=true),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         enum={"male", "female", "non-binary", "prefer_not_to_say"},
 *         nullable=true
 *     ),
 *     @OA\Property(property="is_superhost", type="boolean", example=false, nullable=true),
 *     @OA\Property(property="response_speed", type="number", format="float", example=98.5, nullable=true),
 *     @OA\Property(property="work", type="string", example="Software Engineer", nullable=true),
 *     @OA\Property(property="job_title", type="string", example="Senior Developer", nullable=true),
 *     @OA\Property(property="company", type="string", example="Tech Corp", nullable=true),
 *     @OA\Property(property="school", type="string", example="University of Technology", nullable=true),
 *     @OA\Property(
 *         property="education_level",
 *         type="string",
 *         enum={"high_school", "bachelor", "master", "phd"},
 *         nullable=true
 *     ),
 *     @OA\Property(property="dream_destination", type="string", example="Japan", nullable=true),
 *     @OA\Property(
 *         property="next_destinations",
 *         type="array",
 *
 *         @OA\Items(type="string"),
 *         example={"Italy", "France"},
 *         nullable=true
 *     ),
 *
 *     @OA\Property(property="travel_history", type="boolean", example=true, nullable=true),
 *     @OA\Property(property="favorite_travel_type", type="string", example="adventure", nullable=true),
 *     @OA\Property(property="time_spent_on", type="string", example="Reading", nullable=true),
 *     @OA\Property(property="useless_skill", type="string", example="Can wiggle ears", nullable=true),
 *     @OA\Property(property="pets", type="string", example="Cat named Whiskers", nullable=true),
 *     @OA\Property(property="birth_decade", type="boolean", example=true, nullable=true),
 *     @OA\Property(property="favorite_high_school_song", type="string", example="Sweet Dreams", nullable=true),
 *     @OA\Property(property="fun_fact", type="string", example="Can solve Rubik's cube", nullable=true),
 *     @OA\Property(property="obsession", type="string", example="Coffee", nullable=true),
 *     @OA\Property(property="biography_title", type="string", example="Adventure Seeker", nullable=true),
 *     @OA\Property(
 *         property="languages",
 *         type="array",
 *
 *         @OA\Items(type="string", enum={"en", "uk", "de", "fr"}),
 *         example={"en", "uk"},
 *         nullable=true
 *     ),
 *
 *     @OA\Property(property="about", type="string", example="Passionate traveler and tech enthusiast", nullable=true),
 *     @OA\Property(
 *         property="interests",
 *         type="array",
 *
 *         @OA\Items(
 *             type="string",
 *             enum={"travel", "photography", "food", "sports", "music", "art", "technology"}
 *         ),
 *         example={"travel", "photography"},
 *         nullable=true
 *     ),
 *
 *     @OA\Property(property="facebook_url", type="string", format="url", example="https://facebook.com/johndoe", nullable=true),
 *     @OA\Property(property="instagram_url", type="string", format="url", example="https://instagram.com/johndoe", nullable=true),
 *     @OA\Property(property="twitter_url", type="string", format="url", example="https://twitter.com/johndoe", nullable=true),
 *     @OA\Property(property="linkedin_url", type="string", format="url", example="https://linkedin.com/johndoe", nullable=true),
 *     @OA\Property(property="email_notifications", type="boolean", example=true, nullable=true),
 *     @OA\Property(property="sms_notifications", type="boolean", example=false, nullable=true),
 *     @OA\Property(property="preferred_language", type="string", example="uk", nullable=true),
 *     @OA\Property(property="preferred_currency", type="string", example="UAH", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateProfileRequest",
 *     title="Update Profile Request",
 *     description="Request data for updating an existing profile",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/RequestProfile")
 *     }
 * )
 */
class RequestProfileSchema {}
