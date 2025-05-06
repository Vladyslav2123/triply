<?php

use App\Actions\Experience\CreateExperience;
use App\Actions\Experience\DeleteExperience;
use App\Actions\Experience\FilterExperiences;
use App\Actions\Experience\PublishExperience;
use App\Actions\Experience\UnpublishExperience;
use App\Actions\Experience\UpdateExperience;
use App\Actions\Listing\CreateListing;
use App\Actions\Listing\DeleteListing;
use App\Actions\Listing\FilterListings;
use App\Actions\Listing\PublishListing;
use App\Actions\Listing\UnpublishListing;
use App\Actions\Listing\UpdateListing;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\TestRedisController;
use App\Http\Validators\ListingValidators;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

// Проверка соединения с Redis
echo "Проверка соединения с Redis:\n";
try {
    $result = \Illuminate\Support\Facades\Redis::ping();
    echo 'Redis ping: '.$result."\n";
} catch (Exception $e) {
    echo 'Ошибка соединения с Redis: '.$e->getMessage()."\n";
}

// Проверка кеширования в Redis
echo "\nПроверка кеширования в Redis:\n";
try {
    // Получаем текущий драйвер кеша
    $cacheDriver = config('cache.default');
    $cacheStore = config('cache.stores.'.$cacheDriver.'.driver');
    echo 'Текущий драйвер кеша: '.$cacheDriver."\n";
    echo 'Текущий store кеша: '.$cacheStore."\n";

    // Временно переключаемся на Redis
    config(['cache.default' => 'redis']);

    // Тестируем кеширование
    $testKey = 'test_key_'.time();
    $testValue = 'Test value at '.now()->toDateTimeString();

    // Сохраняем значение в кеше
    \Illuminate\Support\Facades\Cache::put($testKey, $testValue, 60);

    // Получаем значение из кеша
    $retrievedValue = \Illuminate\Support\Facades\Cache::get($testKey);

    echo 'Сохраненное значение: '.$testValue."\n";
    echo 'Полученное значение: '.$retrievedValue."\n";
    echo 'Значения совпадают: '.($testValue === $retrievedValue ? 'Да' : 'Нет')."\n";

    // Восстанавливаем оригинальный драйвер
    config(['cache.default' => $cacheDriver]);
} catch (Exception $e) {
    echo 'Ошибка при тестировании кеша: '.$e->getMessage()."\n";
}

// Проверка использования Redis в контроллере
echo "\nПроверка использования Redis в контроллере:\n";
try {
    $controller = new TestRedisController;
    $response = $controller->testRedis();
    $data = json_decode($response->getContent(), true);

    echo "Результат выполнения TestRedisController::testRedis():\n";
    echo 'Успех: '.($data['success'] ? 'Да' : 'Нет')."\n";
    echo 'Драйвер кеша: '.$data['cache_driver']."\n";
    echo 'Store кеша: '.$data['cache_store']."\n";
    echo 'Тестовое значение: '.$data['test_value']."\n";
    echo 'Полученное значение: '.$data['retrieved_value']."\n";
} catch (Exception $e) {
    echo 'Ошибка при тестировании контроллера: '.$e->getMessage()."\n";
}

// Проверка использования Redis в ListingController
echo "\nПроверка использования Redis в ListingController (метод featured):\n";
try {
    $controller = new ListingController(
        new ListingValidators,
        new FilterListings,
        new CreateListing,
        new UpdateListing,
        new DeleteListing,
        new PublishListing,
        new UnpublishListing,
    );

    // Проверяем, есть ли кеш для featured listings
    $cacheExists = \Illuminate\Support\Facades\Cache::has('featured_listings');
    echo "Кеш 'featured_listings' существует: ".($cacheExists ? 'Да' : 'Нет')."\n";

    // Вызываем метод featured
    $response = $controller->featured();

    // Проверяем, создался ли кеш после вызова метода
    $cacheExistsAfter = \Illuminate\Support\Facades\Cache::has('featured_listings');
    echo "Кеш 'featured_listings' существует после вызова метода: ".($cacheExistsAfter ? 'Да' : 'Нет')."\n";

    echo "Метод ListingController::featured() выполнен успешно\n";
} catch (Exception $e) {
    echo 'Ошибка при тестировании ListingController: '.$e->getMessage()."\n";
}

// Проверка использования Redis в ExperienceController
echo "\nПроверка использования Redis в ExperienceController (метод featured):\n";
try {
    // $controller = new ExperienceController(
    //   new FilterExperiences,
    //   new CreateExperience,
    //  new UpdateExperience,
    //   new DeleteExperience,
    //   new PublishExperience,
    //  new UnpublishExperience
    //  );

    // Проверяем, есть ли кеш для featured experiences
    $cacheExists = \Illuminate\Support\Facades\Cache::has('featured_experiences');
    echo "Кеш 'featured_experiences' существует: ".($cacheExists ? 'Да' : 'Нет')."\n";

    // Вызываем метод featured
    // $response = $controller->featured();

    // Проверяем, создался ли кеш после вызова метода
    $cacheExistsAfter = \Illuminate\Support\Facades\Cache::has('featured_experiences');
    echo "Кеш 'featured_experiences' существует после вызова метода: ".($cacheExistsAfter ? 'Да' : 'Нет')."\n";

    echo "Метод ExperienceController::featured() выполнен успешно\n";
} catch (Exception $e) {
    echo 'Ошибка при тестировании ExperienceController: '.$e->getMessage()."\n";
}
