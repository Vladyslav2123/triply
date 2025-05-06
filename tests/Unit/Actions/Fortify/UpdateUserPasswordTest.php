<?php

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserPassword $updateUserPassword;

    public function test_it_updates_user_password_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $this->updateUserPassword->update($user, [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('current-password', $user->password));
    }

    public function test_it_validates_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $this->expectException(ValidationException::class);

        $this->updateUserPassword->update($user, [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
    }

    public function test_it_validates_password_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $this->expectException(ValidationException::class);

        $this->updateUserPassword->update($user, [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);
    }

    public function test_it_validates_password_length(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $this->expectException(ValidationException::class);

        $this->updateUserPassword->update($user, [
            'current_password' => 'current-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
    }

    public function test_it_requires_current_password_field(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        $this->expectException(ValidationException::class);

        $this->updateUserPassword->update($user, [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateUserPassword = new UpdateUserPassword;
    }
}
