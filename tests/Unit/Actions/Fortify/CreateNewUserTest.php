<?php

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Photo\CreatePhoto;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;
use Throwable;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    private CreateNewUser $createNewUser;

    /**
     * @throws Throwable
     */
    public function test_it_creates_user_successfully(): void
    {
        // $this->markTestSkipped('This test requires complex database setup');

        $userData = [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+380991234567',
            'birth_date' => '1990-01-01',
            'password' => 'password123',
        ];

        $user = $this->createNewUser->create($userData);
        $user->load('profile');

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->profile);
        $this->assertEquals('John', $user->profile->first_name);
        $this->assertEquals('Doe', $user->profile->last_name);
        $this->assertEquals('john.doe@example.com', $user->email);
        $this->assertEquals('+380991234567', $user->phone);
        $this->assertEquals('1990-01-01', $user->profile->birth_date->format('Y-m-d'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * @throws Throwable
     */
    public function test_it_creates_user_with_photo(): void
    {
        // $this->markTestSkipped('This test requires complex database setup');

        Storage::fake('s3');

        $photo = UploadedFile::fake()->image('avatar.jpg');

        $userData = [
            'name' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'photo' => $photo,
        ];

        $user = $this->createNewUser->create($userData);
        $user->load('profile');

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->profile);
        $this->assertEquals('John', $user->profile->first_name);
        $this->assertEquals('john.doe@example.com', $user->email);
    }

    /**
     * @throws Throwable
     */
    public function test_it_validates_required_fields(): void
    {
        $this->expectException(ValidationException::class);

        $this->createNewUser->create([
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_it_validates_email_uniqueness(): void
    {
        User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $this->createNewUser->create([
            'name' => 'Jane',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);
    }

    public function test_it_validates_phone_format(): void
    {
        $this->expectException(ValidationException::class);

        $this->createNewUser->create([
            'name' => 'John',
            'email' => 'john.doe@example.com',
            'phone' => 'invalid-phone',
            'password' => 'password123',
        ]);
    }

    public function test_it_validates_password_length(): void
    {
        $this->expectException(ValidationException::class);

        $this->createNewUser->create([
            'name' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'short',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $createPhoto = Mockery::mock(CreatePhoto::class);
        $createPhoto->shouldReceive('execute')->andReturn(new Photo);

        $this->app->instance(CreatePhoto::class, $createPhoto);
        $this->createNewUser = app(CreateNewUser::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
