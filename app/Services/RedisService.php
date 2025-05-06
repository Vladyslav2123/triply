<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisService
{
    /**
     * Check if Redis is available
     */
    public function isAvailable(): bool
    {
        try {
            Redis::ping();

            return true;
        } catch (Exception $e) {
            Log::error('Redis connection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get Redis connection information
     */
    public function getConnectionInfo(): array
    {
        return [
            'client' => config('database.redis.client'),
            'host' => config('database.redis.default.host'),
            'port' => config('database.redis.default.port'),
            'database' => config('database.redis.default.database'),
            'prefix' => config('database.redis.options.prefix'),
        ];
    }

    /**
     * Get cache configuration
     */
    public function getCacheConfig(): array
    {
        return [
            'driver' => config('cache.default'),
            'store' => config('cache.stores.'.config('cache.default').'.driver'),
            'prefix' => config('cache.prefix'),
        ];
    }

    /**
     * Store a value in cache with tags
     *
     * @param  string|array  $tags
     * @param  mixed  $value
     */
    public function putWithTags($tags, string $key, $value, int $ttl = 3600): bool
    {
        try {
            $tags = is_array($tags) ? $tags : [$tags];
            Cache::tags($tags)->put($key, $value, $ttl);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to store tagged cache value', [
                'tags' => $tags,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get a value from cache with tags
     *
     * @param  string|array  $tags
     * @param  mixed  $default
     * @return mixed
     */
    public function getWithTags($tags, string $key, $default = null)
    {
        try {
            $tags = is_array($tags) ? $tags : [$tags];

            return Cache::tags($tags)->get($key, $default);
        } catch (Exception $e) {
            Log::error('Failed to retrieve tagged cache value', [
                'tags' => $tags,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }

    /**
     * Flush all cache entries with the given tags
     *
     * @param  string|array  $tags
     */
    public function flushTags($tags): bool
    {
        try {
            $tags = is_array($tags) ? $tags : [$tags];
            Cache::tags($tags)->flush();

            return true;
        } catch (Exception $e) {
            Log::error('Failed to flush tagged cache', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remember a value in cache with tags
     *
     * @param  string|array  $tags
     * @return mixed
     */
    public function rememberWithTags($tags, string $key, int $ttl, \Closure $callback)
    {
        try {
            $tags = is_array($tags) ? $tags : [$tags];

            return Cache::tags($tags)->remember($key, $ttl, $callback);
        } catch (Exception $e) {
            Log::error('Failed to remember tagged cache value', [
                'tags' => $tags,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            // If cache fails, execute the callback directly
            return $callback();
        }
    }

    /**
     * Store a value in cache with automatic fallback
     *
     * @param  mixed  $value
     */
    public function safePut(string $key, $value, int $ttl = 3600): bool
    {
        try {
            Cache::put($key, $value, $ttl);

            return true;
        } catch (Exception $e) {
            Log::warning('Cache put failed, using fallback', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            // Store in application memory cache as fallback
            app('cache')->driver('array')->put($key, $value, $ttl);

            return false;
        }
    }

    /**
     * Get a value from cache with automatic fallback
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function safeGet(string $key, $default = null)
    {
        try {
            if (Cache::has($key)) {
                return Cache::get($key, $default);
            }
        } catch (Exception $e) {
            Log::warning('Cache get failed, using fallback', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        // Try to get from application memory cache as fallback
        return app('cache')->driver('array')->get($key, $default);
    }

    /**
     * Remember a value in cache with automatic fallback
     *
     * @return mixed
     */
    public function safeRemember(string $key, int $ttl, \Closure $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (Exception $e) {
            Log::warning('Cache remember failed, using fallback', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            // Try to use application memory cache as fallback
            return app('cache')->driver('array')->remember($key, $ttl, $callback);
        }
    }
}
