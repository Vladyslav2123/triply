<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular users
        User::factory(10)->create();

        // Create admin user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('test'),
            'remember_token' => Str::random(10),
            'role' => UserRole::ADMIN->value,
        ]);
    }
}
