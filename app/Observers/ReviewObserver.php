<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $host = $review->reservation->reservationable->host;
        $profile = $host->profile;

        // Оновлюємо обидві метрики одразу
        $profile->increment('reviews_count');

        $averageRating = Review::whereHas('reservation.reservationable', function ($query) use ($host) {
            $query->where('host_id', $host->id);
        })->avg('overall_rating');

        $profile->update(['rating' => $averageRating]);
    }

    public function deleted(Review $review): void
    {
        $host = $review->reservation->reservationable->host;
        $profile = $host->profile;

        $profile->decrement('reviews_count');

        // Перераховуємо середній рейтинг
        $averageRating = Review::whereHas('reservation.reservationable', function ($query) use ($host) {
            $query->where('host_id', $host->id);
        })->avg('overall_rating');

        $profile->update(['rating' => $averageRating ?? 0]);
    }
}
