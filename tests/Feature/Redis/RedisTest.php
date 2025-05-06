<?php

namespace Tests\Feature\Redis;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class RedisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic Redis connection
     */
    public function test_redis_connection(): void
    {
        try {
            $result = Redis::ping();
            $this->assertEquals('PONG', $result);
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis connection failed: '.$e->getMessage());
        }
    }

    /**
     * Test Redis cache functionality
     */
    public function test_redis_cache(): void
    {
        // Set cache driver to Redis for this test
        $originalDriver = config('cache.default');
        config(['cache.default' => 'redis']);

        try {
            $testKey = 'test_key_'.time();
            $testValue = 'Test value at '.now()->toDateTimeString();

            // Store value in cache
            Cache::put($testKey, $testValue, 60);

            // Retrieve value from cache
            $retrievedValue = Cache::get($testKey);

            // Assert values match
            $this->assertEquals($testValue, $retrievedValue);

            // Test cache expiration (with a very short TTL)
            $expireKey = 'expire_test_'.time();
            Cache::put($expireKey, 'Expiring value', 1);

            // Value should exist immediately
            $this->assertTrue(Cache::has($expireKey));

            // Wait for expiration
            sleep(2);

            // Value should be gone after TTL
            $this->assertFalse(Cache::has($expireKey));
        } finally {
            // Restore original cache driver
            config(['cache.default' => $originalDriver]);
        }
    }

    /**
     * Test Redis cache tags
     */
    public function test_redis_cache_tags(): void
    {
        // Set cache driver to Redis for this test
        $originalDriver = config('cache.default');
        config(['cache.default' => 'redis']);

        try {
            $tag = 'test-tag-'.time();
            $testKey = 'tagged_test_key';
            $testValue = 'Tagged value at '.now()->toDateTimeString();

            // Store tagged value
            Cache::tags([$tag])->put($testKey, $testValue, 60);

            // Retrieve tagged value
            $retrievedValue = Cache::tags([$tag])->get($testKey);

            // Assert values match
            $this->assertEquals($testValue, $retrievedValue);

            // Flush the tag
            Cache::tags([$tag])->flush();

            // Value should be gone after tag flush
            $this->assertNull(Cache::tags([$tag])->get($testKey));
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis tags test failed: '.$e->getMessage());
        } finally {
            // Restore original cache driver
            config(['cache.default' => $originalDriver]);
        }
    }

    /**
     * Test Redis cache increment/decrement operations
     */
    public function test_redis_increment_decrement(): void
    {
        // Set cache driver to Redis for this test
        $originalDriver = config('cache.default');
        config(['cache.default' => 'redis']);

        try {
            $counterKey = 'counter_test_'.time();

            // Initialize counter
            Cache::put($counterKey, 5, 60);

            // Increment
            $result = Cache::increment($counterKey, 3);
            $this->assertEquals(8, $result);

            // Verify value
            $this->assertEquals(8, Cache::get($counterKey));

            // Decrement
            $result = Cache::decrement($counterKey, 2);
            $this->assertEquals(6, $result);

            // Verify value
            $this->assertEquals(6, Cache::get($counterKey));
        } finally {
            // Restore original cache driver
            config(['cache.default' => $originalDriver]);
        }
    }

    /**
     * Test Redis cache remember function
     */
    public function test_redis_remember(): void
    {
        // Set cache driver to Redis for this test
        $originalDriver = config('cache.default');
        config(['cache.default' => 'redis']);

        try {
            $rememberKey = 'remember_test_'.time();

            // Use remember to cache a value
            $value = Cache::remember($rememberKey, 60, function () {
                return 'Remembered value at '.now()->toDateTimeString();
            });

            // Verify the value was stored
            $this->assertEquals($value, Cache::get($rememberKey));

            // Call remember again - should return cached value, not regenerate
            $secondValue = Cache::remember($rememberKey, 60, function () {
                return 'New value that should not be used';
            });

            // Verify we got the original value, not the new one
            $this->assertEquals($value, $secondValue);
        } finally {
            // Restore original cache driver
            config(['cache.default' => $originalDriver]);
        }
    }

    /**
     * Test Redis API endpoints
     */
    public function test_redis_api_endpoints(): void
    {
        // Test basic Redis endpoint
        $response = $this->getJson('/api/v1/test-redis');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'cache_driver',
                'cache_store',
                'test_value',
                'retrieved_value',
            ]);

        // Test connection endpoint
        $response = $this->getJson('/api/v1/test-redis/connection');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'ping_result',
                'redis_client',
                'redis_host',
                'redis_port',
            ]);

        // Test custom operation endpoint
        $response = $this->postJson('/api/v1/test-redis/custom', [
            'operation' => 'increment',
            'key' => 'test_counter',
            'value' => 5,
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'operation',
                'key',
                'value',
                'result',
            ]);
    }
}
