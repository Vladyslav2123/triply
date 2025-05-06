<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Проверка соединения с Redis
echo "Проверка соединения с Redis:\n";
try {
    $result = \Illuminate\Support\Facades\Redis::ping();
    echo 'Redis ping: '.$result."\n";
} catch (\Exception $e) {
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
} catch (\Exception $e) {
    echo 'Ошибка при тестировании кеша: '.$e->getMessage()."\n";
}

// Проверка использования Redis в контроллере
echo "\nПроверка использования Redis в контроллере:\n";
try {
    $controller = new \App\Http\Controllers\TestRedisController;
    $response = $controller->testRedis();
    $data = json_decode($response->getContent(), true);

    echo "Результат выполнения TestRedisController::testRedis():\n";
    echo 'Успех: '.($data['success'] ? 'Да' : 'Нет')."\n";
    echo 'Драйвер кеша: '.$data['cache_driver']."\n";
    echo 'Store кеша: '.$data['cache_store']."\n";
    echo 'Тестовое значение: '.$data['test_value']."\n";
    echo 'Полученное значение: '.$data['retrieved_value']."\n";
} catch (\Exception $e) {
    echo 'Ошибка при тестировании контроллера: '.$e->getMessage()."\n";
}

// Проверка кеша для featured listings
echo "\nПроверка кеша для featured listings:\n";
try {
    // Проверяем, есть ли кеш для featured listings
    $cacheExists = \Illuminate\Support\Facades\Cache::has('featured_listings');
    echo "Кеш 'featured_listings' существует: ".($cacheExists ? 'Да' : 'Нет')."\n";

    // Если кеш существует, получаем его содержимое
    if ($cacheExists) {
        $cachedData = \Illuminate\Support\Facades\Cache::get('featured_listings');
        echo "Кеш 'featured_listings' содержит ".count($cachedData)." элементов\n";
    }
} catch (\Exception $e) {
    echo 'Ошибка при проверке кеша featured_listings: '.$e->getMessage()."\n";
}

// Проверка кеша для featured experiences
echo "\nПроверка кеша для featured experiences:\n";
try {
    // Проверяем, есть ли кеш для featured experiences
    $cacheExists = \Illuminate\Support\Facades\Cache::has('featured_experiences');
    echo "Кеш 'featured_experiences' существует: ".($cacheExists ? 'Да' : 'Нет')."\n";

    // Если кеш существует, получаем его содержимое
    if ($cacheExists) {
        $cachedData = \Illuminate\Support\Facades\Cache::get('featured_experiences');
        echo "Кеш 'featured_experiences' содержит ".count($cachedData)." элементов\n";
    }
} catch (\Exception $e) {
    echo 'Ошибка при проверке кеша featured_experiences: '.$e->getMessage()."\n";
}

// Проверка работы с тегами в Redis
echo "\nПроверка работы с тегами в Redis:\n";
try {
    $tag = 'test-tag-'.time();
    $testKey = 'tagged_test_key';
    $testValue = 'Tagged value at '.now()->toDateTimeString();

    // Сохраняем значение с тегом
    \Illuminate\Support\Facades\Cache::tags([$tag])->put($testKey, $testValue, 60);

    // Получаем значение с тегом
    $retrievedValue = \Illuminate\Support\Facades\Cache::tags([$tag])->get($testKey);

    echo 'Сохраненное значение с тегом: '.$testValue."\n";
    echo 'Полученное значение с тегом: '.$retrievedValue."\n";
    echo 'Значения совпадают: '.($testValue === $retrievedValue ? 'Да' : 'Нет')."\n";

    // Очищаем кеш с тегом
    \Illuminate\Support\Facades\Cache::tags([$tag])->flush();

    // Проверяем, что значение удалено
    $valueAfterFlush = \Illuminate\Support\Facades\Cache::tags([$tag])->get($testKey);
    echo 'Значение после очистки тега: '.($valueAfterFlush === null ? 'null (удалено)' : $valueAfterFlush)."\n";
} catch (\Exception $e) {
    echo 'Ошибка при тестировании тегов: '.$e->getMessage()."\n";
}
