<?php

namespace App\OpenApi\Schemas\Profile;

/**
 * @OA\Schema(
 *     schema="ProfileUpdateRequest",
 *     title="Запит на оновлення профілю",
 *     description="Запит на оновлення даних профілю користувача",
 *
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
 *         property="country",
 *         type="string",
 *         description="Країна",
 *         example="Україна",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="city",
 *         type="string",
 *         description="Місто",
 *         example="Київ",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         description="Адреса",
 *         example="вул. Хрещатик, 1",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="postal_code",
 *         type="string",
 *         description="Поштовий індекс",
 *         example="01001",
 *         nullable=true
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
 *         example=true,
 *         nullable=true
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
 *         example=true,
 *         nullable=true
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
 *         property="facebook_url",
 *         type="string",
 *         description="Посилання на Facebook",
 *         example="https://facebook.com/username",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="instagram_url",
 *         type="string",
 *         description="Посилання на Instagram",
 *         example="https://instagram.com/username",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="twitter_url",
 *         type="string",
 *         description="Посилання на Twitter",
 *         example="https://twitter.com/username",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="linkedin_url",
 *         type="string",
 *         description="Посилання на LinkedIn",
 *         example="https://linkedin.com/in/username",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="email_notifications",
 *         type="boolean",
 *         description="Чи увімкнені email-сповіщення",
 *         example=true,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="sms_notifications",
 *         type="boolean",
 *         description="Чи увімкнені SMS-сповіщення",
 *         example=false,
 *         nullable=true
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
 *         property="about",
 *         type="string",
 *         description="Інформація про користувача",
 *         example="Люблю подорожувати та знайомитися з новими людьми.",
 *         nullable=true
 *     )
 * )
 */
class ProfileUpdateRequestSchema {}
