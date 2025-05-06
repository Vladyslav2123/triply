<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestRedisController extends Controller
{
    /**
     * Test basic Redis cache functionality
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testRedis()
    {
        try {
            $cacheDriver = config('cache.default');
            $cacheStore = config('cache.stores.'.$cacheDriver.'.driver');

            $testValue = 'Test value at '.now()->toDateTimeString();

            Cache::put('test_redis_key', $testValue, 60);

            $retrievedValue = Cache::get('test_redis_key');

            $success = $testValue === $retrievedValue;

            return response()->json([
                'success' => $success,
                'cache_driver' => $cacheDriver,
                'cache_store' => $cacheStore,
                'test_value' => $testValue,
                'retrieved_value' => $retrievedValue,
                'redis_host' => config('database.redis.default.host'),
                'redis_port' => config('database.redis.default.port'),
                'cache_prefix' => config('cache.prefix'),
            ]);
        } catch (Exception $e) {
            Log::error('Redis test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'cache_driver' => config('cache.default'),
                'cache_store' => config('cache.stores.'.config('cache.default').'.driver'),
            ], 500);
        }
    }

    /**
     * Test Redis connection directly using the Redis facade
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            // Test direct Redis connection
            $pingResult = Redis::connection()->ping();

            return response()->json([
                'success' => true,
                'ping_result' => $pingResult,
                'redis_client' => config('database.redis.client'),
                'redis_host' => config('database.redis.default.host'),
                'redis_port' => config('database.redis.default.port'),
                'redis_database' => config('database.redis.default.database'),
            ]);
        } catch (Exception $e) {
            Log::error('Redis connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test Redis cache with tags
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testTags()
    {
        try {
            $tag = 'test-tag';
            $testValue = 'Tagged value at '.now()->toDateTimeString();

            Cache::tags([$tag])->put('tagged_test_key', $testValue, 60);

            $retrievedValue = Cache::tags([$tag])->get('tagged_test_key');

            $success = $testValue === $retrievedValue;

            return response()->json([
                'success' => $success,
                'tag' => $tag,
                'test_value' => $testValue,
                'retrieved_value' => $retrievedValue,
            ]);
        } catch (Exception $e) {
            Log::error('Redis tags test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Note: Redis cache tags require Redis >= 2.8.0 with the Redis PHP extension',
            ], 500);
        }
    }

    /**
     * Test Redis cache expiration
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testExpiration()
    {
        try {
            $testValue = 'Expiring value at '.now()->toDateTimeString();
            $expirationKey = 'expiring_test_key';

            // Store with 5 seconds expiration
            Cache::put($expirationKey, $testValue, 5);

            // Check immediately
            $immediateValue = Cache::get($expirationKey);
            $immediateExists = Cache::has($expirationKey);

            // Wait 6 seconds
            sleep(6);

            // Check after expiration
            $afterValue = Cache::get($expirationKey);
            $afterExists = Cache::has($expirationKey);

            return response()->json([
                'test_value' => $testValue,
                'immediate_check' => [
                    'exists' => $immediateExists,
                    'value' => $immediateValue,
                ],
                'after_expiration' => [
                    'exists' => $afterExists,
                    'value' => $afterValue,
                ],
                'success' => $immediateExists && ! $afterExists,
            ]);
        } catch (Exception $e) {
            Log::error('Redis expiration test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test Redis cache with custom operations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customOperation(Request $request)
    {
        try {
            $operation = $request->input('operation', 'increment');
            $key = $request->input('key', 'counter');
            $value = $request->input('value', 1);

            $result = null;

            switch ($operation) {
                case 'increment':
                    $result = Cache::increment($key, $value);
                    break;

                case 'decrement':
                    $result = Cache::decrement($key, $value);
                    break;

                case 'remember':
                    $result = Cache::remember($key, 60, function () use ($value) {
                        return 'Remembered value: '.$value;
                    });
                    break;

                case 'forget':
                    $result = Cache::forget($key);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid operation',
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'operation' => $operation,
                'key' => $key,
                'value' => $value,
                'result' => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Redis custom operation test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
