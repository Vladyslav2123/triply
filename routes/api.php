<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ExperienceAvailabilityController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TestRedisController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Redis testing routes
    Route::prefix('test-redis')->group(function () {
        Route::get('/', [TestRedisController::class, 'testRedis'])->name('test.redis');
        Route::get('/connection', [TestRedisController::class, 'testConnection'])->name('test.redis.connection');
        Route::get('/tags', [TestRedisController::class, 'testTags'])->name('test.redis.tags');
        Route::get('/expiration', [TestRedisController::class, 'testExpiration'])->name('test.redis.expiration');
        Route::post('/custom', [TestRedisController::class, 'customOperation'])->name('test.redis.custom');
    });

    Route::post('/send-otp', [RegisterController::class, 'sendOtp'])->name('auth.send-otp');

    Route::prefix('auth')->group(function () {
        Route::post('/login-otp', [RegisterController::class, 'loginOtp'])->name('auth.login-otp');
        Route::post('/register-otp', [RegisterController::class, 'registerOtp'])->name('auth.register-otp');
    });

    Route::post('/users/exists', [UserController::class, 'exists'])->name('users.exists');

    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingController::class, 'index'])->name('listings.index');
        Route::get('/featured', [ListingController::class, 'featured'])->name('listings.featured');

        Route::get('/{listing:slug}', [ListingController::class, 'show'])->name('listings.show');
        Route::get('/{listing:slug}/similar', [ListingController::class, 'similar'])->name('listings.similar');
    });

    Route::get('/hosts/{hostId}/listings', [ListingController::class, 'byHost'])->name('hosts.listings');

    Route::prefix('experiences')->group(function () {
        Route::get('/', [ExperienceController::class, 'index'])->name('experiences.index');
        Route::get('/featured', [ExperienceController::class, 'featured'])->name('experiences.featured');

        Route::get('/{experience:slug}', [ExperienceController::class, 'show'])->name('experiences.show');
    });

    Route::get('/hosts/{hostId}/experiences', [ExperienceController::class, 'byHost'])->name('hosts.experiences');

    Route::get('/profiles/{id}', [ProfileController::class, 'showById'])->name('profiles.show');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load(['profile']));
    })->name('user.profile');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/', [ProfileController::class, 'store'])->name('profile.store');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::patch('/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
        Route::patch('/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
        Route::patch('/verify', [ProfileController::class, 'verify'])->name('profile.verify');

        Route::post('/photo', [ProfilePhotoController::class, 'store'])->name('profile.photo.store');
        Route::delete('/photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/{user:id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user:id}/profile', [ProfileController::class, 'showById'])->name('users.profile');
    });

    Route::prefix('listings')->group(function () {
        Route::post('/', [ListingController::class, 'store'])->name('listings.store');
        Route::put('/{listing:slug}', [ListingController::class, 'update'])->name('listings.update');
        Route::patch('/{listing:slug}', [ListingController::class, 'update'])->name('listings.patch');
        Route::delete('/{listing:slug}', [ListingController::class, 'destroy'])->name('listings.destroy');

        Route::post('/{listing:slug}/favorite', [FavoriteController::class, 'toggleFavorite'])->name('listings.favorite');
        Route::get('/{listing:slug}/reviews', [ReviewController::class, 'listingReviews'])->name('listings.reviews');
        Route::post('/{listing:slug}/reviews', [ReviewController::class, 'createListingReview'])->name('listings.reviews.store');
        Route::post('/{listing:slug}/reserve', [ReservationController::class, 'store'])->name('listings.reserve');
        Route::patch('/{listing:slug}/publish', [ListingController::class, 'publish'])->name('listings.publish');
        Route::patch('/{listing:slug}/unpublish', [ListingController::class, 'unpublish'])->name('listings.unpublish');
    });

    Route::prefix('experiences')->group(function () {
        Route::post('/', [ExperienceController::class, 'store'])->name('experiences.store');
        Route::put('/{experience:slug}', [ExperienceController::class, 'update'])->name('experiences.update');
        Route::patch('/{experience:slug}', [ExperienceController::class, 'update'])->name('experiences.patch');
        Route::delete('/{experience:slug}', [ExperienceController::class, 'destroy'])->name('experiences.destroy');

        Route::post('/{experience:slug}/favorite', [FavoriteController::class, 'toggleFavorite'])->name('experiences.favorite');
        Route::get('/{experience:slug}/is-favorited', [FavoriteController::class, 'isFavorited'])->name('experiences.is-favorited');
        Route::get('/{experience:slug}/favorite-count', [FavoriteController::class, 'getFavoriteCount'])->name('experiences.favorite-count');
        Route::get('/{experience:slug}/reviews', [ReviewController::class, 'experienceReviews'])->name('experiences.reviews');
        Route::post('/{experience:slug}/reviews', [ReviewController::class, 'createExperienceReview'])->name('experiences.reviews.store');
        Route::post('/{experience:slug}/reserve', [ReservationController::class, 'store'])->name('experiences.reserve');
        Route::patch('/{experience:slug}/publish', [ExperienceController::class, 'publish'])->name('experiences.publish');
        Route::patch('/{experience:slug}/unpublish', [ExperienceController::class, 'unpublish'])->name('experiences.unpublish');

        Route::get('/{experience:slug}/availability', [ExperienceAvailabilityController::class, 'index'])->name('experiences.availability.index');
        Route::post('/{experience:slug}/availability', [ExperienceAvailabilityController::class, 'store'])->name('experiences.availability.store');
        Route::get('/{experience:slug}/check-availability', [ExperienceAvailabilityController::class, 'check'])->name('experiences.availability.check');
    });

    Route::apiResource('reservations', ReservationController::class);
    Route::prefix('reservations')->group(function () {
        Route::patch('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::patch('/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    });

    Route::apiResource('reviews', ReviewController::class);

    Route::apiResource('favorites', FavoriteController::class);

    Route::apiResource('photos', PhotoController::class);
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Not Found',
        'status' => 404,
    ], 404);
});
