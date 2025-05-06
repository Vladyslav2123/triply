<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $this->command->info("Починаємо оновлення профілів для {$users->count()} користувачів");

        foreach ($users as $user) {
            $profile = $user->profile;

            if ($profile) {
                $profile->delete();
                $this->command->info("Видалено існуючий профіль для користувача: {$user->id}");
            }

            ProfileFactory::new()
                ->forUser($user)
                ->create();

            $this->command->info("Створено новий профіль для користувача: {$user->id}");
        }

        $this->command->info('Завершено оновлення профілів для всіх користувачів');
    }
}
