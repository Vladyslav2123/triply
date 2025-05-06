# Використання автентифікації в OpenAPI документації

## Загальна інформація

У нашому проекті використовується сесійна автентифікація з Laravel Sanctum (web guard) та X-XSRF-TOKEN для CSRF захисту. Ця документація пояснює, як правильно використовувати цю автентифікацію в схемах OpenAPI.

## Налаштування автентифікації

Основне налаштування автентифікації знаходиться в файлі `app/Http/Controllers/Controller.php`:

```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="sessionAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-XSRF-TOKEN",
 *     description="Додаток використовує сесійну автентифікацію з Laravel Sanctum (web guard) та X-XSRF-TOKEN для CSRF захисту. Клієнти повинні включати X-XSRF-TOKEN заголовок у всі запити, що змінюють дані."
 * )
 */
```

## Як використовувати автентифікацію в контролерах

Для захисту ендпоінтів, які потребують автентифікації, додайте параметр `security` до анотації OpenAPI:

```php
/**
 * @OA\Get(
 *     path="/api/v1/profile",
 *     summary="Отримати профіль користувача",
 *     tags={"Profile"},
 *     security={{"sessionAuth":{}}},  // Додайте цей рядок для захищених ендпоінтів
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Успішна операція",
 *         @OA\JsonContent(ref="#/components/schemas/Profile")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизований доступ",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 */
```

## Приклади використання

### 1. Для GET запитів, які отримують дані користувача

```php
/**
 * @OA\Get(
 *     path="/api/v1/user",
 *     summary="Отримати дані поточного користувача",
 *     tags={"User"},
 *     security={{"sessionAuth":{}}},
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Успішна операція",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизований доступ",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 */
```

### 2. Для POST/PUT/DELETE запитів, які змінюють дані

```php
/**
 * @OA\Post(
 *     path="/api/v1/listings",
 *     summary="Створити нове оголошення",
 *     tags={"Listings"},
 *     security={{"sessionAuth":{}}},
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ListingRequest")
 *     ),
 *     
 *     @OA\Response(
 *         response=201,
 *         description="Оголошення створено",
 *         @OA\JsonContent(ref="#/components/schemas/Listing")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизований доступ",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Помилка валідації",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 */
```

## Важливі моменти

1. **Завжди додавайте відповідь 401** для ендпоінтів, які потребують автентифікації:

```php
@OA\Response(
    response=401,
    description="Неавторизований доступ",
    @OA\JsonContent(ref="#/components/schemas/Error")
)
```

2. **Для ендпоінтів з авторизацією** (перевірка прав доступу) додавайте також відповідь 403:

```php
@OA\Response(
    response=403,
    description="Заборонено",
    @OA\JsonContent(ref="#/components/schemas/Error")
)
```

3. **Для публічних ендпоінтів** не потрібно додавати параметр `security`.

## Генерація документації

Після оновлення анотацій OpenAPI, згенеруйте документацію за допомогою команди:

```bash
php artisan l5-swagger:generate
```

Документація буде доступна за адресою `/api/documentation`.
