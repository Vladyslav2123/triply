<?php

namespace App\Models\Traits\Methods;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait HasMethods
 *
 * Містить всі методи для моделі User.
 */
trait HasMethods
{
    /**
     * Get the user's active listings.
     */
    public function activeListings(): Collection
    {
        return $this->listingsQuery()->active()->get();
    }

    /**
     * Get the user's featured listings.
     */
    public function featuredListings(): Collection
    {
        return $this->listingsQuery()->featured()->get();
    }

    /**
     * Get the user's draft listings.
     */
    public function draftListings(): Collection
    {
        return $this->listingsQuery()->draft()->get();
    }

    /**
     * Get the user's upcoming reservations.
     */
    public function upcomingReservations(): Collection
    {
        return $this->reservationsQuery()->upcoming()->withReservationable()->get();
    }

    /**
     * Get the user's past reservations.
     */
    public function pastReservations(): Collection
    {
        return $this->reservationsQuery()->past()->withReservationable()->get();
    }

    /**
     * Get the user's current reservations (staying now).
     */
    public function currentReservations(): Collection
    {
        return $this->reservationsQuery()->current()->withReservationable()->get();
    }

    /**
     * Get the user's average rating as a host.
     */
    public function getAverageRating(): float
    {
        return (float) $this->receivedReviews()->avg('overall_rating') ?: 0.0;
    }

    /**
     * Get reviews received by this user (as a host).
     */
    public function receivedReviews(): Builder
    {
        return Review::whereHas('reservation', function ($query) {
            $query->whereHasMorph(
                'reservationable',
                [Listing::class, Experience::class],
                function ($query) {
                    $query->where('host_id', $this->id);
                }
            );
        })->with('reviewer');
    }

    /**
     * Get the total number of reviews received as a host.
     */
    public function getReviewsCount(): int
    {
        return $this->receivedReviews()->count();
    }

    /**
     * Get all messages (sent and received) for this user.
     */
    public function allMessages(): Builder
    {
        return Message::where('sender_id', $this->id)
            ->orWhere('recipient_id', $this->id)
            ->orderByDesc('created_at');
    }

    /**
     * Get the count of unread messages.
     */
    public function getUnreadMessagesCount(): int
    {
        return $this->unreadMessages()->count();
    }

    /**
     * Get unread messages for this user.
     */
    public function unreadMessages(): Builder
    {
        return $this->receivedMessages()
            ->whereNull('read_at')
            ->orderByDesc('created_at');
    }

    /**
     * Get conversations (grouped by other user).
     *
     * @return Collection
     */
    public function getConversations()
    {
        $sentMessages = $this->sentMessages()->with('recipient')->get()
            ->groupBy('recipient_id');

        $receivedMessages = $this->receivedMessages()->with('sender')->get()
            ->groupBy('sender_id');

        // Combine and sort by latest message
        $conversations = collect();

        foreach ($sentMessages as $userId => $messages) {
            $conversations[$userId] = [
                'user' => $messages->first()->recipient,
                'latest_message' => $messages->sortByDesc('created_at')->first(),
                'unread_count' => 0,
            ];
        }

        foreach ($receivedMessages as $userId => $messages) {
            if (isset($conversations[$userId])) {
                $latestSent = $conversations[$userId]['latest_message'];
                $latestReceived = $messages->sortByDesc('created_at')->first();

                if ($latestReceived->created_at > $latestSent->created_at) {
                    $conversations[$userId]['latest_message'] = $latestReceived;
                }

                $conversations[$userId]['unread_count'] = $messages->whereNull('read_at')->count();
            } else {
                $conversations[$userId] = [
                    'user' => $messages->first()->sender,
                    'latest_message' => $messages->sortByDesc('created_at')->first(),
                    'unread_count' => $messages->whereNull('read_at')->count(),
                ];
            }
        }

        return $conversations->sortByDesc(function ($conversation) {
            return $conversation['latest_message']->created_at;
        });
    }
}
