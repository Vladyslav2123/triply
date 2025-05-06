<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiControllerTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
        ]);
    }

    /**
     * Перевіряє структуру відповіді API
     *
     * @param  array  $response  Відповідь API
     * @param  array  $structure  Очікувана структура
     */
    protected function assertApiResponse(array $response, array $structure): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);

        foreach ($structure as $key) {
            $this->assertArrayHasKey($key, $response['data']);
        }
    }

    /**
     * Перевіряє структуру помилки API
     *
     * @param  array  $response  Відповідь API
     * @param  int  $code  Код помилки
     */
    protected function assertApiError(array $response, int $code): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals($code, $response['code']);
    }

    /**
     * Перевіряє структуру пагінації
     *
     * @param  array  $response  Відповідь API
     */
    protected function assertApiPagination(array $response): void
    {
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('current_page', $response['meta']);
        $this->assertArrayHasKey('last_page', $response['meta']);
        $this->assertArrayHasKey('per_page', $response['meta']);
        $this->assertArrayHasKey('total', $response['meta']);

        $this->assertArrayHasKey('links', $response);
        $this->assertArrayHasKey('first', $response['links']);
        $this->assertArrayHasKey('last', $response['links']);
        $this->assertArrayHasKey('prev', $response['links']);
        $this->assertArrayHasKey('next', $response['links']);
    }
}
