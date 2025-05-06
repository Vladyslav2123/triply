<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\Schema(
 *     schema="Profile",
 *     title="Профіль користувача",
 *     description="Модель профілю користувача з детальною інформацією",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор профілю",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         format="ulid",
 *         description="ID користувача, якому належить профіль",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
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
 *         property="gender",
 *         type="string",
 *         enum={"male", "female", "non-binary", "prefer_not_to_say"},
 *         description="Стать користувача",
 *         example="male",
 *         nullable=true
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
 *         property="work",
 *         type="string",
 *         description="Місце роботи",
 *         example="IT компанія",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="job_title",
 *         type="string",
 *         description="Посада",
 *         example="Розробник програмного забезпечення",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="company",
 *         type="string",
 *         description="Назва компанії",
 *         example="Triply",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="school",
 *         type="string",
 *         description="Навчальний заклад",
 *         example="Київський національний університет",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="education_level",
 *         type="string",
 *         enum={"high_school", "bachelor", "master", "phd"},
 *         description="Рівень освіти",
 *         example="master",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="dream_destination",
 *         type="string",
 *         description="Омріяне місце для подорожі",
 *         example="Японія",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="next_destinations",
 *         type="array",
 *         description="Наступні місця для подорожі",
 *
 *         @OA\Items(type="string"),
 *         example={"Італія", "Франція"},
 *         nullable=true
 *     ),
 *
 *     @OA\Property(
 *         property="travel_history",
 *         type="boolean",
 *         description="Чи має користувач історію подорожей",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="favorite_travel_type",
 *         type="string",
 *         description="Улюблений тип подорожей",
 *         example="пляжний відпочинок",
 *         nullable=true
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
 *         property="time_spent_on",
 *         type="string",
 *         description="На що витрачає час",
 *         example="Читання книг",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="useless_skill",
 *         type="string",
 *         description="Безкорисна навичка",
 *         example="Можу рухати вухами",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="pets",
 *         type="string",
 *         description="Домашні тварини",
 *         example="Кіт на ім'я Мурчик",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="birth_decade",
 *         type="boolean",
 *         description="Чи показувати десятиліття народження замість повної дати",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="favorite_high_school_song",
 *         type="string",
 *         description="Улюблена пісня зі школи",
 *         example="Sweet Dreams",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="fun_fact",
 *         type="string",
 *         description="Цікавий факт про користувача",
 *         example="Можу розв'язати кубик Рубіка за хвилину",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="obsession",
 *         type="string",
 *         description="Захоплення",
 *         example="Кава",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="biography_title",
 *         type="string",
 *         description="Заголовок біографії",
 *         example="Шукач пригод",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="interests",
 *         type="array",
 *         description="Інтереси користувача",
 *
 *         @OA\Items(type="string"),
 *         example={"фотографія", "піші прогулянки"},
 *         nullable=true
 *     ),
 *
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
 *         property="email_notifications",
 *         type="boolean",
 *         description="Чи увімкнені email-сповіщення",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="sms_notifications",
 *         type="boolean",
 *         description="Чи увімкнені SMS-сповіщення",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="preferred_language",
 *         type="string",
 *         description="Бажана мова інтерфейсу",
 *         example="uk",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="preferred_currency",
 *         type="string",
 *         description="Бажана валюта",
 *         example="UAH",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="is_verified",
 *         type="boolean",
 *         description="Чи верифікований профіль",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата верифікації",
 *         example="2023-01-15T14:30:00.000000Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="verification_method",
 *         type="string",
 *         enum={"email", "phone", "document"},
 *         description="Метод верифікації",
 *         example="email",
 *         nullable=true
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
 *         property="about",
 *         type="string",
 *         description="Інформація про користувача",
 *         example="Люблю подорожувати та знайомитися з новими людьми.",
 *         nullable=true
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
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата видалення профілю (soft delete)",
 *         example="2023-06-01T00:00:00.000000Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="photo",
 *         nullable=true,
 *         ref="#/components/schemas/PhotoBasic",
 *         description="Фото профілю"
 *     )
 * )
 */
class ProfileSchema {}
