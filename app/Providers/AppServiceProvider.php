<?php

namespace App\Providers;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');

        Model::unguard();

        Relation::enforceMorphMap([
            'user' => User::class,
            'profile' => Profile::class,
            'photo' => Photo::class,
            'listing' => Listing::class,
            'experience' => Experience::class,
            'favorite' => Favorite::class,
            'review' => Review::class,
            'reservation' => Reservation::class,
        ]);
    }
}
