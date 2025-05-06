<?php

namespace App\OpenApi\Schemas\Listing;

/**
 * @OA\Schema(
 *     schema="ListingDetail",
 *     title="Детальна інформація про оголошення",
 *     description="Розширена інформація про оголошення з додатковими даними",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Listing"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="host",
 *                 type="object",
 *                 description="Інформація про власника",
 *                 @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVU"),
 *                 @OA\Property(property="name", type="string", example="Олександр Петренко"),
 *                 @OA\Property(property="avatar", type="string", format="url", example="https://api.triply.blog/storage/users/avatar.jpg"),
 *                 @OA\Property(property="rating", type="number", format="float", example=4.9),
 *                 @OA\Property(property="reviews_count", type="integer", example=120),
 *                 @OA\Property(property="is_superhost", type="boolean", example=true),
 *                 @OA\Property(property="response_rate", type="integer", example=98),
 *                 @OA\Property(property="response_time", type="string", example="within an hour"),
 *                 @OA\Property(property="joined_at", type="string", format="date", example="2020-05-15")
 *             ),
 *             @OA\Property(
 *                 property="photos",
 *                 type="array",
 *                 description="Фотографії оголошення",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVV"),
 *                     @OA\Property(property="url", type="string", format="url", example="https://api.triply.blog/storage/listings/photo1.jpg"),
 *                     @OA\Property(property="caption", type="string", example="Вітальня з панорамними вікнами"),
 *                     @OA\Property(property="is_primary", type="boolean", example=true),
 *                     @OA\Property(property="order", type="integer", example=1)
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="reviews",
 *                 type="array",
 *                 description="Останні відгуки про оголошення",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVW"),
 *                     @OA\Property(property="user", type="object",
 *                         @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVX"),
 *                         @OA\Property(property="name", type="string", example="Марія Іваненко"),
 *                         @OA\Property(property="avatar", type="string", format="url", example="https://api.triply.blog/storage/users/user1.jpg")
 *                     ),
 *                     @OA\Property(property="content", type="string", example="Чудове місце для відпочинку! Все відповідає опису, господар дуже привітний."),
 *                     @OA\Property(property="overall_rating", type="number", format="float", example=4.8),
 *                     @OA\Property(property="cleanliness_rating", type="number", format="float", example=5.0),
 *                     @OA\Property(property="communication_rating", type="number", format="float", example=4.5),
 *                     @OA\Property(property="check_in_rating", type="number", format="float", example=5.0),
 *                     @OA\Property(property="accuracy_rating", type="number", format="float", example=4.5),
 *                     @OA\Property(property="location_rating", type="number", format="float", example=5.0),
 *                     @OA\Property(property="value_rating", type="number", format="float", example=4.5),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-03-15T14:30:00Z")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="availability",
 *                 type="array",
 *                 description="Доступність оголошення для бронювання",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="date", type="string", format="date", example="2023-07-15"),
 *                     @OA\Property(property="is_available", type="boolean", example=true),
 *                     @OA\Property(property="price", type="number", format="float", nullable=true, example=1500)
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="similar_listings",
 *                 type="array",
 *                 description="Схожі оголошення",
 *
 *                 @OA\Items(
 *                     type="object",
 *
 *                     @OA\Property(property="id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVY"),
 *                     @OA\Property(property="slug", type="string", example="modern-studio-in-kyiv"),
 *                     @OA\Property(property="title", type="string", example="Сучасна студія в Києві"),
 *                     @OA\Property(property="price_per_night", type="object",
 *                         @OA\Property(property="amount", type="integer", example=1200),
 *                         @OA\Property(property="currency", type="string", example="UAH"),
 *                         @OA\Property(property="formatted", type="string", example="1 200,00 ₴")
 *                     ),
 *                     @OA\Property(property="rating", type="number", format="float", example=4.6),
 *                     @OA\Property(property="photo", type="string", format="url", example="https://api.triply.blog/storage/listings/similar1.jpg")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="url",
 *                 type="string",
 *                 format="url",
 *                 description="URL оголошення",
 *                 example="https://api.triply.blog/api/v1/listings/cozy-apartment-in-kyiv"
 *             ),
 *             @OA\Property(
 *                 property="is_favorite",
 *                 type="boolean",
 *                 description="Чи додано оголошення до обраного поточним користувачем",
 *                 example=false
 *             )
 *         )
 *     }
 * )
 */
class ListingDetailSchema {}
