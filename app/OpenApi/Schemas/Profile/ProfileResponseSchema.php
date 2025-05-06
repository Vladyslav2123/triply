<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\Schema(
 *     schema="ProfileResponse",
 *     title="Відповідь з даними профілю",
 *     description="Повна відповідь з даними профілю та інформацією про користувача",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор профілю",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="time_on_site",
 *         type="object",
 *         description="Статистика часу користувача на платформі",
 *         @OA\Property(property="years", type="integer", example=2, description="Кількість років на платформі"),
 *         @OA\Property(property="months", type="integer", example=5, description="Додаткові місяці на платформі"),
 *         @OA\Property(property="joined_at", type="string", format="date-time", example="2021-08-15T10:00:00.000000Z", description="Точна дата та час реєстрації")
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="Інформація про пов'язаного користувача",
 *         allOf={
 *
 *             @OA\Schema(ref="#/components/schemas/UserBasicInfo"),
 *             @OA\Schema(
 *
 *                 @OA\Property(
 *                     property="photo",
 *                     nullable=true,
 *                     ref="#/components/schemas/PhotoBasic"
 *                 )
 *             )
 *         }
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         description="Ім'я користувача",
 *         example="Іван",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="Прізвище користувача",
 *         example="Петренко",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Повне ім'я користувача",
 *         example="Іван Петренко"
 *     ),
 *     @OA\Property(
 *         property="birth_date",
 *         type="string",
 *         format="date",
 *         description="Дата народження",
 *         example="1990-01-01",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="age",
 *         type="integer",
 *         description="Вік користувача (обчислюється)",
 *         example=33,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         enum={"male", "female", "non-binary", "prefer_not_to_say"},
 *         description="Стать користувача",
 *         example="male",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         description="Інформація про місцезнаходження",
 *         @OA\Property(property="country", type="string", example="Україна", nullable=true),
 *         @OA\Property(property="city", type="string", example="Київ", nullable=true),
 *         @OA\Property(property="address", type="string", example="вул. Хрещатик, 1", nullable=true),
 *         @OA\Property(property="postal_code", type="string", example="01001", nullable=true),
 *         @OA\Property(property="formatted", type="string", example="вул. Хрещатик, 1, Київ, 01001, Україна")
 *     ),
 *     @OA\Property(
 *         property="is_superhost",
 *         type="boolean",
 *         description="Чи є користувач суперхостом",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="response_speed",
 *         type="number",
 *         format="float",
 *         description="Швидкість відповіді (у відсотках)",
 *         example=98.5
 *     ),
 *     @OA\Property(
 *         property="views_count",
 *         type="integer",
 *         description="Кількість переглядів профілю",
 *         example=1000
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="number",
 *         format="float",
 *         description="Рейтинг користувача",
 *         example=4.8
 *     ),
 *     @OA\Property(
 *         property="reviews_count",
 *         type="integer",
 *         description="Кількість відгуків про користувача",
 *         example=25
 *     ),
 *     @OA\Property(
 *         property="work_info",
 *         type="object",
 *         description="Інформація про роботу",
 *         @OA\Property(property="work", type="string", example="IT компанія", nullable=true),
 *         @OA\Property(property="job_title", type="string", example="Розробник програмного забезпечення", nullable=true),
 *         @OA\Property(property="company", type="string", example="Triply", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="education_info",
 *         type="object",
 *         description="Інформація про освіту",
 *         @OA\Property(property="school", type="string", example="Київський національний університет", nullable=true),
 *         @OA\Property(
 *             property="education_level",
 *             type="string",
 *             enum={"high_school", "bachelor", "master", "phd"},
 *             example="master",
 *             nullable=true
 *         )
 *     ),
 *     @OA\Property(
 *         property="travel_preferences",
 *         type="object",
 *         description="Уподобання щодо подорожей",
 *         @OA\Property(property="dream_destination", type="string", example="Японія", nullable=true),
 *         @OA\Property(
 *             property="next_destinations",
 *             type="array",
 *
 *             @OA\Items(type="string"),
 *             example={"Італія", "Франція"},
 *             nullable=true
 *         ),
 *
 *         @OA\Property(property="travel_history", type="boolean", example=true),
 *         @OA\Property(property="favorite_travel_type", type="string", example="пляжний відпочинок", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="languages",
 *         type="array",
 *         description="Мови, якими володіє користувач",
 *
 *         @OA\Items(type="string", enum={"en", "uk", "de", "fr"}),
 *         example={"uk", "en"},
 *         nullable=true
 *     ),
 *
 *     @OA\Property(
 *         property="personal_details",
 *         type="object",
 *         description="Особисті деталі",
 *         @OA\Property(property="time_spent_on", type="string", example="Читання книг", nullable=true),
 *         @OA\Property(property="useless_skill", type="string", example="Можу рухати вухами", nullable=true),
 *         @OA\Property(property="pets", type="string", example="Кіт на ім'я Мурчик", nullable=true),
 *         @OA\Property(property="birth_decade", type="boolean", example=true),
 *         @OA\Property(property="favorite_high_school_song", type="string", example="Sweet Dreams", nullable=true),
 *         @OA\Property(property="fun_fact", type="string", example="Можу розв'язати кубик Рубіка за хвилину", nullable=true),
 *         @OA\Property(property="obsession", type="string", example="Кава", nullable=true),
 *         @OA\Property(property="biography_title", type="string", example="Шукач пригод", nullable=true),
 *         @OA\Property(
 *             property="interests",
 *             type="array",
 *
 *             @OA\Items(type="string"),
 *             example={"фотографія", "піші прогулянки"},
 *             nullable=true
 *         ),
 *
 *         @OA\Property(property="about", type="string", example="Люблю подорожувати та знайомитися з новими людьми.", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="social_links",
 *         type="object",
 *         description="Посилання на соціальні мережі",
 *         @OA\Property(property="facebook", type="string", example="https://facebook.com/username", nullable=true),
 *         @OA\Property(property="instagram", type="string", example="https://instagram.com/username", nullable=true),
 *         @OA\Property(property="twitter", type="string", example="https://twitter.com/username", nullable=true),
 *         @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/username", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="preferences",
 *         type="object",
 *         description="Налаштування користувача",
 *         @OA\Property(property="email_notifications", type="boolean", example=true),
 *         @OA\Property(property="sms_notifications", type="boolean", example=false),
 *         @OA\Property(property="preferred_language", type="string", example="uk", nullable=true),
 *         @OA\Property(property="preferred_currency", type="string", example="UAH", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="verification",
 *         type="object",
 *         description="Інформація про верифікацію",
 *         @OA\Property(property="is_verified", type="boolean", example=true),
 *         @OA\Property(
 *             property="verified_at",
 *             type="string",
 *             format="date-time",
 *             example="2023-01-15T14:30:00.000000Z",
 *             nullable=true
 *         ),
 *         @OA\Property(
 *             property="verification_method",
 *             type="string",
 *             enum={"email", "phone", "document"},
 *             example="email",
 *             nullable=true
 *         )
 *     ),
 *     @OA\Property(
 *         property="last_active_at",
 *         type="string",
 *         format="date-time",
 *         description="Час останньої активності",
 *         example="2023-05-20T10:15:30.000000Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="photo",
 *         nullable=true,
 *         ref="#/components/schemas/PhotoBasic",
 *         description="Фото профілю"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата створення профілю",
 *         example="2023-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата останнього оновлення профілю",
 *         example="2023-05-15T12:30:45.000000Z"
 *     )
 * )
 */
class ProfileResponseSchema {}
