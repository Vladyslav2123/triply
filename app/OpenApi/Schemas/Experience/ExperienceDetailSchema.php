<?php

namespace App\OpenApi\Schemas\Experience;

/**
 * @OA\Schema(
 *     schema="ExperienceDetail",
 *     title="Деталі враження",
 *     description="Детальна інформація про враження",
 *
 *     @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q4"),
 *     @OA\Property(property="slug", type="string", example="cooking-class-in-kyiv"),
 *     @OA\Property(property="title", type="string", example="Кулінарний майстер-клас у Києві"),
 *     @OA\Property(property="description", type="string", example="Навчіться готувати традиційні українські страви"),
 *     @OA\Property(property="host_id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q5"),
 *     @OA\Property(property="category", type="string", example="food_and_drink"),
 *     @OA\Property(property="sub_category", type="string", example="cooking_class"),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         @OA\Property(property="country", type="string", example="Україна"),
 *         @OA\Property(property="city", type="string", example="Київ"),
 *         @OA\Property(property="state", type="string", example="Київська область"),
 *         @OA\Property(property="street", type="string", example="вул. Хрещатик, 1"),
 *         @OA\Property(
 *             property="coordinates",
 *             type="object",
 *             @OA\Property(property="latitude", type="number", format="float", example=50.4501),
 *             @OA\Property(property="longitude", type="number", format="float", example=30.5234)
 *         )
 *     ),
 *     @OA\Property(
 *         property="languages",
 *         type="array",
 *
 *         @OA\Items(type="string", example="uk")
 *     ),
 *
 *     @OA\Property(property="status", type="string", example="published"),
 *     @OA\Property(property="is_featured", type="boolean", example=true),
 *     @OA\Property(property="views_count", type="integer", example=1250),
 *     @OA\Property(property="rating", type="number", format="float", example=4.8),
 *     @OA\Property(property="reviews_count", type="integer", example=42),
 *     @OA\Property(
 *         property="pricing",
 *         type="object",
 *         @OA\Property(property="base_price", type="number", format="float", example=500),
 *         @OA\Property(property="currency", type="string", example="UAH")
 *     ),
 *     @OA\Property(
 *         property="grouping",
 *         type="object",
 *         @OA\Property(property="min_guests", type="integer", example=2),
 *         @OA\Property(property="max_guests", type="integer", example=10)
 *     ),
 *     @OA\Property(property="duration", type="string", format="date-time", example="2023-12-01T14:00:00Z"),
 *     @OA\Property(property="location_type", type="string", example="indoor"),
 *     @OA\Property(property="location_note", type="string", example="Зустріч біля входу в будівлю"),
 *     @OA\Property(
 *         property="host_bio",
 *         type="object",
 *         @OA\Property(property="about", type="string", example="Професійний шеф-кухар з 10-річним досвідом"),
 *         @OA\Property(property="experience", type="string", example="Працювала в найкращих ресторанах Києва")
 *     ),
 *     @OA\Property(
 *         property="host_provides",
 *         type="object",
 *         @OA\Property(property="items", type="array", @OA\Items(type="string", example="Всі необхідні інгредієнти")),
 *         @OA\Property(property="notes", type="string", example="Будь ласка, повідомте про алергії заздалегідь")
 *     ),
 *     @OA\Property(
 *         property="guest_needs",
 *         type="object",
 *         @OA\Property(property="items", type="array", @OA\Items(type="string", example="Зручний одяг")),
 *         @OA\Property(property="notes", type="string", example="Все інше буде надано")
 *     ),
 *     @OA\Property(
 *         property="guest_requirements",
 *         type="object",
 *         @OA\Property(property="min_age", type="integer", example=18),
 *         @OA\Property(property="skill_level", type="string", example="beginner"),
 *         @OA\Property(property="physical_activity", type="string", example="low")
 *     ),
 *     @OA\Property(
 *         property="host",
 *         type="object",
 *         @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q5"),
 *         @OA\Property(property="name", type="string", example="Олена Петренко"),
 *         @OA\Property(property="profile_photo", type="string", nullable=true, example="https://example.com/photos/user.jpg"),
 *         @OA\Property(property="bio", type="string", example="Професійний шеф-кухар з 10-річним досвідом"),
 *         @OA\Property(property="languages", type="array", @OA\Items(type="string", example="uk")),
 *         @OA\Property(property="rating", type="number", format="float", example=4.9),
 *         @OA\Property(property="reviews_count", type="integer", example=120)
 *     ),
 *     @OA\Property(
 *         property="photos",
 *         type="array",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q6"),
 *             @OA\Property(property="url", type="string", example="https://example.com/photos/experience.jpg"),
 *             @OA\Property(property="is_primary", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Property(
 *         property="availability",
 *         type="array",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(property="date", type="string", format="date", example="2023-12-01"),
 *             @OA\Property(property="time", type="string", format="time", example="14:00:00"),
 *             @OA\Property(property="available_spots", type="integer", example=8)
 *         )
 *     ),
 *     @OA\Property(
 *         property="reviews",
 *         type="object",
 *         @OA\Property(property="average_rating", type="number", format="float", example=4.8),
 *         @OA\Property(property="count", type="integer", example=42),
 *         @OA\Property(
 *             property="recent",
 *             type="array",
 *
 *             @OA\Items(
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q7"),
 *                 @OA\Property(property="content", type="string", example="Чудовий досвід! Дуже рекомендую."),
 *                 @OA\Property(property="rating", type="number", format="float", example=5),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-15T10:30:00Z"),
 *                 @OA\Property(
 *                     property="reviewer",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="ulid", example="01HJD7NWGN7XXZSY2SJRJVT1Q8"),
 *                     @OA\Property(property="name", type="string", example="Іван Коваленко"),
 *                     @OA\Property(property="profile_photo", type="string", nullable=true, example="https://example.com/photos/user2.jpg")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class ExperienceDetailSchema {}
