<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view reviews, even guests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Review $review): bool
    {
        return true; // Anyone can view a review, even guests
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Only authenticated users can create reviews (middleware handles authentication)
    }

    /**
     * Determine whether the user can create a review for a specific reviewable entity.
     *
     * @param  User  $user  The user attempting to create the review
     * @param  Model  $reviewable  The entity to be reviewed (Listing or Experience)
     */
    public function createFor(User $user, Model $reviewable): Response
    {
        return $this->checkReviewEligibility($user, $reviewable);
    }

    /**
     * Determine whether the user can create a review for a specific listing.
     *
     * @param  User  $user  The user attempting to create the review
     * @param  Listing  $listing  The listing to be reviewed
     */
    public function createForListing(User $user, Listing $listing): Response
    {
        return $this->checkReviewEligibility($user, $listing);
    }

    /**
     * Determine whether the user can create a review for a specific experience.
     *
     * @param  User  $user  The user attempting to create the review
     * @param  Experience  $experience  The experience to be reviewed
     */
    public function createForExperience(User $user, Experience $experience): Response
    {
        return $this->checkReviewEligibility($user, $experience);
    }

    /**
     * Check if a user is eligible to review a reservationable entity.
     *
     * @param  User  $user  The user attempting to create the review
     * @param  Model  $reviewable  The entity to be reviewed
     */
    private function checkReviewEligibility(User $user, Model $reviewable): Response
    {
        $morphMap = Relation::morphMap();
        $reviewableType = array_search(get_class($reviewable), $morphMap);

        if (! $reviewableType || ! in_array($reviewableType, ['listing', 'experience'])) {
            return Response::deny('Непідтримуваний тип об\'єкту для відгуку.');
        }

        // Check if the user has a completed reservation without a review
        $hasEligibleReservation = $user->reservations()
            ->where('reservationable_id', $reviewable->id)
            ->where('reservationable_type', get_class($reviewable))
            ->where('status', ReservationStatus::COMPLETED)
            ->whereDoesntHave('review')
            ->exists();

        if (! $hasEligibleReservation) {
            return Response::deny('Ви повинні мати завершене бронювання без відгуку, щоб залишити відгук.');
        }

        // Check if the user is not the owner of the reviewable entity
        if (method_exists($reviewable, 'isOwnedBy') && $reviewable->isOwnedBy($user)) {
            return Response::deny('Ви не можете залишити відгук для власного об\'єкту.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): Response
    {
        // Only the reviewer can update their own review
        if ($user->id !== $review->reviewer_id) {
            return Response::deny('Ви можете редагувати тільки власні відгуки.');
        }

        // Check if the review is too old to edit (e.g., older than 30 days)
        $editDeadline = now()->subDays(30);
        if ($review->created_at->lt($editDeadline)) {
            return Response::deny('Відгуки можна редагувати тільки протягом 30 днів після створення.');
        }

        // Check if the reservation is still valid
        if (! $review->reservation || $review->reservation->status !== ReservationStatus::COMPLETED) {
            return Response::deny('Відгук можна редагувати тільки для завершеного бронювання.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): Response
    {
        // The reviewer can delete their own review within 30 days
        if ($user->id === $review->reviewer_id) {
            $deleteDeadline = now()->subDays(30);
            if ($review->created_at->lt($deleteDeadline)) {
                return Response::deny('Відгуки можна видалити тільки протягом 30 днів після створення.');
            }

            return Response::allow();
        }

        // The owner of the reservationable entity can delete reviews for their entity
        $reservationable = $review->reservation?->reservationable;
        if ($reservationable && method_exists($reservationable, 'isOwnedBy') && $reservationable->isOwnedBy($user)) {
            return Response::allow();
        }

        // Admins can delete any review
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('У вас немає прав для видалення цього відгуку.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратори можуть відновлювати видалені відгуки.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратори можуть остаточно видаляти відгуки.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can report the review.
     */
    public function report(User $user, Review $review): Response
    {
        // Users can't report their own reviews
        if ($user->id === $review->reviewer_id) {
            return Response::deny('Ви не можете поскаржитись на власний відгук.');
        }

        // Check if the review is too old to report (e.g., older than 90 days)
        $reportDeadline = now()->subDays(90);
        if ($review->created_at->lt($reportDeadline)) {
            return Response::deny('На відгук можна поскаржитись тільки протягом 90 днів після створення.');
        }

        // Check if the user has already reported this review
        if ($review->reports()->where('user_id', $user->id)->exists()) {
            return Response::deny('Ви вже скаржились на цей відгук.');
        }

        return Response::allow();
    }
}
