<?php

namespace Tests;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Налаштування перед запуском тестів.
     */
    protected function setUp(): void
    {
        // Configure morph map before application setup
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

        parent::setUp();

        // Database configuration
        $this->app['config']->set('database.default', 'pgsql');
        $this->app['config']->set('database.connections.pgsql.host', '127.0.0.1');
        $this->app['config']->set('database.connections.pgsql.port', '5432');
        $this->app['config']->set('database.connections.pgsql.database', 'mybnb');
        $this->app['config']->set('database.connections.pgsql.username', 'postgres');
        $this->app['config']->set('database.connections.pgsql.password', '');

        // Run migrations
        Artisan::call('migrate:fresh');
    }

    /**
     * Створює тестового користувача.
     *
     * @param  array  $attributes  Додаткові атрибути для користувача
     * @return User Створений користувач
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Створює тестового адміністратора.
     *
     * @param  array  $attributes  Додаткові атрибути для адміністратора
     * @return User Створений адміністратор
     */
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => true,
        ], $attributes));
    }
}
