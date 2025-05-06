<?php

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResetUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private ResetUserPassword $resetUserPassword;

    public function test_it_resets_user_password_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->resetUserPassword->reset($user, [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
    }

    public function test_it_validates_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        $this->resetUserPassword->reset($user, [
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);
    }

    public function test_it_validates_password_length(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        $this->resetUserPassword->reset($user, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
    }

    public function test_it_requires_password_field(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        $this->resetUserPassword->reset($user, [
            //
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetUserPassword = new ResetUserPassword;
    }
}
