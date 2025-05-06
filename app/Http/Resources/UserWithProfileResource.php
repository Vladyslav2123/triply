<?php

namespace App\Http\Resources;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserWithProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role?->value,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),

            'listings_count' => $this->whenCounted('listings'),
            'reservations_count' => $this->whenCounted('reservations'),
            'reviews_count' => $this->whenCounted('reviews'),
            'favorites_count' => $this->whenCounted('favorites'),

            'time_on_site' => $this->getTimeOnSite(),

            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'is_superhost' => $this->profile->is_superhost,
                    'response_speed' => $this->profile->response_speed,
                    'gender' => $this->profile->gender?->value,

                    'views_count' => $this->views_count,
                    'rating' => $this->rating,
                    'reviews_count' => $this->reviews_count,

                    'location' => $this->profile->location,

                    'work' => $this->profile->work,
                    'job_title' => $this->profile->job_title,
                    'company' => $this->profile->company,
                    'school' => $this->profile->school,
                    'education_level' => $this->profile->education_level?->value,

                    'dream_destination' => $this->profile->dream_destination,
                    'next_destinations' => $this->profile->next_destinations,
                    'travel_history' => $this->profile->travel_history,
                    'favorite_travel_type' => $this->profile->favorite_travel_type,

                    'about' => $this->profile->about,
                    'languages' => $this->profile->languages?->map(fn ($lang) => $lang->value),
                    'interests' => $this->profile->interests?->map(fn ($interest) => $interest->value),

                    'time_spent_on' => $this->profile->time_spent_on,
                    'useless_skill' => $this->profile->useless_skill,
                    'pets' => $this->profile->pets,
                    'birth_decade' => $this->profile->birth_decade,
                    'favorite_high_school_song' => $this->profile->favorite_high_school_song,
                    'fun_fact' => $this->profile->fun_fact,
                    'obsession' => $this->profile->obsession,
                    'biography_title' => $this->profile->biography_title,

                    'social_links' => $this->profile->social_links,

                    'email_notifications' => $this->profile->email_notifications,
                    'sms_notifications' => $this->profile->sms_notifications,
                    'preferred_language' => $this->profile->preferred_language,
                    'preferred_currency' => $this->profile->preferred_currency,

                    'is_verified' => $this->profile->is_verified,
                    'verified_at' => $this->profile->verified_at?->format('Y-m-d H:i:s'),
                    'verification_method' => $this->profile->verification_method,
                    'last_active_at' => $this->profile->last_active_at?->format('Y-m-d H:i:s'),
                    'photo' => $this->whenLoaded('photo', function () {
                        return new PhotoResource($this->photo);
                    }),
                ];
            }),
        ];
    }

    /**
     * Calculate the time the user has been on the site.
     */
    protected function getTimeOnSite(): array
    {
        try {
            $createdAt = $this->created_at;
            $now = now();

            if (! $createdAt || $createdAt > $now) {
                return [
                    'years' => 0,
                    'months' => 0,
                    'days' => 0,
                    'formatted' => 'Сьогодні',
                ];
            }

            $years = $now->diffInYears($createdAt);
            $months = $now->copy()->subYears($years)->diffInMonths($createdAt);
            $days = $now->copy()->subYears($years)->subMonths($months)->diffInDays($createdAt);

            return [
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'formatted' => $this->formatTimeOnSite($years, $months, $days),
            ];
        } catch (Exception $e) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => 'Сьогодні',
            ];
        }
    }

    /**
     * Format the time on site into a human-readable string.
     */
    protected function formatTimeOnSite(int $years, int $months, int $days): string
    {
        $parts = [];

        if ($years > 0) {
            $parts[] = $years.' '.trans_choice('рік|роки|років', $years);
        }

        if ($months > 0) {
            $parts[] = $months.' '.trans_choice('місяць|місяці|місяців', $months);
        }

        if ($days > 0 && count($parts) < 2) {
            $parts[] = $days.' '.trans_choice('день|дні|днів', $days);
        }

        return empty($parts) ? 'Сьогодні' : implode(' ', $parts);
    }
}
