<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */
class ProfileResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'slug' => $this->user->slug,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'created_at' => $this->user->created_at->format('Y-m-d H:i:s'),
            ],

            'time_on_site' => $this->getTimeOnSite(),

            // Основна інформація
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->getFullName(),
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'is_superhost' => $this->is_superhost,
            'response_speed' => $this->response_speed,
            'gender' => $this->gender?->value,

            // Додаткові метрики
            'views_count' => $this->views_count,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,

            // Робота та освіта
            'work' => $this->work,
            'job_title' => $this->job_title,
            'company' => $this->company,
            'school' => $this->school,
            'education_level' => $this->education_level?->value,

            // Подорожі
            'dream_destination' => $this->dream_destination,
            'next_destinations' => $this->next_destinations,
            'travel_history' => $this->travel_history,
            'favorite_travel_type' => $this->favorite_travel_type,

            // Особисті дані
            'time_spent_on' => $this->time_spent_on,
            'useless_skill' => $this->useless_skill,
            'pets' => $this->pets,
            'birth_decade' => $this->birth_decade,
            'favorite_high_school_song' => $this->favorite_high_school_song,
            'fun_fact' => $this->fun_fact,
            'obsession' => $this->obsession,
            'biography_title' => $this->biography_title,

            // Мови та інтереси
            'languages' => $this->languages?->map(fn ($lang) => $lang->value),
            'about' => $this->about,
            'interests' => $this->interests?->map(fn ($interest) => $interest->value),

            // Адреса
            'location' => $this->location,

            // Соціальні мережі
            'social_links' => $this->social_links,

            // Налаштування
            'email_notifications' => $this->email_notifications,
            'sms_notifications' => $this->sms_notifications,
            'preferred_language' => $this->preferred_language,
            'preferred_currency' => $this->preferred_currency,

            // Верифікація
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'verification_method' => $this->verification_method,

            'last_active_at' => $this->last_active_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Calculate the time the user has been on the site.
     */
    protected function getTimeOnSite(): array
    {
        try {
            $createdAt = $this->user->created_at;
            $now = now();

            // Перевірка на коректність дати
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
            // Повертаємо безпечні значення у випадку помилки
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

    /**
     * Get the full name from first and last name.
     */
    protected function getFullName(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: 'Невідомий користувач';
    }
}
