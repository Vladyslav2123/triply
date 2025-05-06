<?php

namespace App\OpenApi\Schemas\Listing;

/**
 * @OA\Schema(
 *     schema="Listing",
 *     title="Оголошення",
 *     description="Модель оголошення про житло",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         description="Унікальний ідентифікатор оголошення",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="URL-сумісний ідентифікатор оголошення",
 *         example="cozy-apartment-in-kyiv"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Назва оголошення",
 *         example="Затишна квартира в центрі Києва"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="object",
 *         description="Детальний опис оголошення",
 *         @OA\Property(
 *             property="listing_description",
 *             type="string",
 *             description="Загальний опис житла",
 *             example="Чудова квартира з видом на місто"
 *         ),
 *         @OA\Property(
 *             property="your_property",
 *             type="string",
 *             description="Опис особливостей житла",
 *             example="Простора вітальня та спальня"
 *         ),
 *         @OA\Property(
 *             property="guest_access",
 *             type="string",
 *             description="Інформація про доступ гостей",
 *             example="Повний доступ до всієї квартири"
 *         ),
 *         @OA\Property(
 *             property="interaction_with_guests",
 *             type="string",
 *             description="Інформація про взаємодію з гостями",
 *             example="Я завжди на зв'язку через месенджери"
 *         ),
 *         @OA\Property(
 *             property="other_details",
 *             type="string",
 *             description="Інші важливі деталі",
 *             example="Поруч є супермаркет та аптека"
 *         ),
 *         @OA\Property(
 *             property="neighborhood",
 *             type="string",
 *             description="Опис району",
 *             example="Тихий центральний район з хорошою інфраструктурою"
 *         ),
 *         @OA\Property(
 *             property="transportation",
 *             type="string",
 *             description="Інформація про транспорт",
 *             example="5 хвилин до метро, зупинка автобуса поруч"
 *         ),
 *         @OA\Property(
 *             property="highlights",
 *             type="array",
 *             description="Ключові особливості житла",
 *
 *             @OA\Items(type="string"),
 *             example={"Панорамні вікна", "Нова техніка", "Безкоштовна парковка"}
 *         )
 *     ),
 *
 *     @OA\Property(
 *         property="price_per_night",
 *         type="object",
 *         description="Ціна за ніч",
 *         @OA\Property(property="amount", type="integer", example=1500),
 *         @OA\Property(property="currency", type="string", example="UAH"),
 *         @OA\Property(property="formatted", type="string", example="1 500,00 ₴")
 *     ),
 *     @OA\Property(
 *         property="discounts",
 *         type="object",
 *         description="Знижки на проживання",
 *         @OA\Property(property="weekly", type="integer", description="Знижка за тиждень у відсотках", example=10),
 *         @OA\Property(property="monthly", type="integer", description="Знижка за місяць у відсотках", example=20),
 *         @OA\Property(property="last_minute", type="integer", description="Знижка за бронювання в останню хвилину", example=5),
 *         @OA\Property(property="early_bird", type="integer", description="Знижка за раннє бронювання", example=7)
 *     ),
 *     @OA\Property(
 *         property="accept_guests",
 *         type="object",
 *         description="Інформація про прийом гостей",
 *         @OA\Property(property="adults", type="boolean", example=true),
 *         @OA\Property(property="children", type="boolean", example=true),
 *         @OA\Property(property="pets", type="boolean", example=false),
 *         @OA\Property(property="max_adults", type="integer", example=4),
 *         @OA\Property(property="max_children", type="integer", example=2),
 *         @OA\Property(property="max_pets", type="integer", example=0),
 *         @OA\Property(property="pets_restrictions", type="string", example="Домашні тварини не дозволені")
 *     ),
 *     @OA\Property(
 *         property="rooms_rules",
 *         type="object",
 *         description="Правила щодо кімнат",
 *         @OA\Property(property="floors_count", type="integer", example=1),
 *         @OA\Property(property="floor_listing", type="integer", example=3),
 *         @OA\Property(property="year_built", type="integer", example=2010),
 *         @OA\Property(property="property_size", type="number", format="float", example=75.5),
 *         @OA\Property(property="elevator", type="boolean", example=true),
 *         @OA\Property(property="ceiling_height", type="number", format="float", example=2.7),
 *         @OA\Property(property="furnished", type="boolean", example=true)
 *     ),
 *     @OA\Property(
 *         property="subtype",
 *         type="string",
 *         description="Підтип житла",
 *         example="apartment"
 *     ),
 *     @OA\Property(
 *         property="amenities",
 *         type="array",
 *         description="Зручності в житлі",
 *
 *         @OA\Items(type="string"),
 *         example={"wifi", "kitchen", "washer", "air_conditioning", "heating"}
 *     ),
 *
 *     @OA\Property(
 *         property="accessibility_features",
 *         type="object",
 *         description="Особливості доступності",
 *         @OA\Property(property="step_free_entrance", type="boolean", example=true),
 *         @OA\Property(property="wide_doorways", type="boolean", example=true),
 *         @OA\Property(property="step_free_path_to_room", type="boolean", example=true),
 *         @OA\Property(property="accessible_bathroom", type="boolean", example=false),
 *         @OA\Property(property="accessible_parking", type="boolean", example=true)
 *     ),
 *     @OA\Property(
 *         property="availability_settings",
 *         type="object",
 *         description="Налаштування доступності для бронювання",
 *         @OA\Property(property="min_stay", type="integer", example=2),
 *         @OA\Property(property="max_stay", type="integer", example=30),
 *         @OA\Property(property="available_from", type="string", format="date", example="2023-06-01"),
 *         @OA\Property(property="available_to", type="string", format="date", example="2023-12-31"),
 *         @OA\Property(property="instant_booking", type="boolean", example=true),
 *         @OA\Property(property="advance_booking_days", type="integer", example=90)
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         description="Місцезнаходження житла",
 *         @OA\Property(
 *             property="address",
 *             type="object",
 *             @OA\Property(property="country", type="string", example="Україна"),
 *             @OA\Property(property="state", type="string", example="Київська область"),
 *             @OA\Property(property="city", type="string", example="Київ"),
 *             @OA\Property(property="street", type="string", example="вул. Хрещатик"),
 *             @OA\Property(property="house_number", type="string", example="10"),
 *             @OA\Property(property="apartment", type="string", example="5"),
 *             @OA\Property(property="postal_code", type="string", example="01001")
 *         ),
 *         @OA\Property(
 *             property="coordinates",
 *             type="object",
 *             @OA\Property(property="latitude", type="number", format="float", example=50.4501),
 *             @OA\Property(property="longitude", type="number", format="float", example=30.5234)
 *         )
 *     ),
 *     @OA\Property(
 *         property="house_rules",
 *         type="object",
 *         description="Правила проживання",
 *         @OA\Property(property="pets_allowed", type="boolean", example=false),
 *         @OA\Property(property="events_allowed", type="boolean", example=false),
 *         @OA\Property(property="smoking_allowed", type="boolean", example=false),
 *         @OA\Property(property="quiet_hours", type="boolean", example=true),
 *         @OA\Property(property="commercial_photography_allowed", type="boolean", example=false),
 *         @OA\Property(property="number_of_guests", type="integer", example=4),
 *         @OA\Property(property="additional_rules", type="string", example="Будь ласка, знімайте взуття при вході."),
 *         @OA\Property(property="check_in_time", type="string", example="14:00"),
 *         @OA\Property(property="check_out_time", type="string", example="12:00")
 *     ),
 *     @OA\Property(
 *         property="guest_safety",
 *         type="object",
 *         description="Засоби безпеки для гостей",
 *         @OA\Property(property="smoke_detector", type="boolean", example=true),
 *         @OA\Property(property="fire_extinguisher", type="boolean", example=true),
 *         @OA\Property(property="security_camera", type="boolean", example=false),
 *         @OA\Property(property="first_aid_kit", type="boolean", example=true),
 *         @OA\Property(property="carbon_monoxide_detector", type="boolean", example=true),
 *         @OA\Property(property="emergency_exit", type="boolean", example=true)
 *     ),
 *     @OA\Property(
 *         property="host_id",
 *         type="string",
 *         format="ulid",
 *         description="Ідентифікатор власника житла",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVU"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Тип житла",
 *         enum={"apartment", "house", "hotel", "guesthouse", "villa", "cottage", "hostel"},
 *         example="apartment"
 *     ),
 *     @OA\Property(
 *         property="listing_type",
 *         type="string",
 *         description="Тип оголошення",
 *         enum={"entire_place", "room", "shared_room"},
 *         example="entire_place"
 *     ),
 *     @OA\Property(
 *         property="advance_notice_type",
 *         type="string",
 *         description="Тип попереднього повідомлення",
 *         enum={"same_day", "one_day", "two_days"},
 *         example="one_day"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Статус оголошення",
 *         enum={"draft", "pending", "published", "rejected", "suspended", "archived"},
 *         example="published"
 *     ),
 *     @OA\Property(
 *         property="is_published",
 *         type="boolean",
 *         description="Чи опубліковане оголошення",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_featured",
 *         type="boolean",
 *         description="Чи є оголошення рекомендованим",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="views_count",
 *         type="integer",
 *         description="Кількість переглядів оголошення",
 *         example=1250
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="number",
 *         format="float",
 *         description="Середній рейтинг оголошення",
 *         example=4.8
 *     ),
 *     @OA\Property(
 *         property="reviews_count",
 *         type="integer",
 *         description="Кількість відгуків про оголошення",
 *         example=25
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата створення оголошення",
 *         example="2023-01-15T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата останнього оновлення оголошення",
 *         example="2023-02-20T15:30:00Z"
 *     )
 * )
 */
class ListingSchema {}
