<?php

namespace App\Actions\Fortify;

use App\Actions\Photo\CreatePhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Throwable;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private readonly CreatePhoto $createPhoto
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws Throwable
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'surname' => ['nullable', 'string', 'max:255', 'min:3'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users,phone'],
            'birth_date' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:8'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ])->validate();

        $existingUser = User::where('email', $input['email'])->first();
        if ($existingUser) {
            Log::warning('Attempted to create user with existing email', [
                'email' => $input['email'],
                'existing_user_id' => $existingUser->id,
            ]);
            throw new ValidationException(Validator::make([], []), [
                'email' => ['The email has already been taken.'],
            ]);
        }

        return DB::transaction(function () use ($input) {
            Log::info('Creating new user', [
                'email' => $input['email'],
                'phone' => $input['phone'] ?? null,
            ]);

            $user = new User([
                'email' => $input['email'],
                'phone' => $input['phone'] ?? null,
                'password' => Hash::make($input['password']),
            ]);

            $user->generateSlug();
            $user->save();

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $profile = $user->getOrCreateProfile();
            $profile->update([
                'first_name' => $input['name'],
                'last_name' => $input['surname'] ?? null,
                'birth_date' => $input['birth_date'] ?? null,
            ]);

            Log::info('Profile created for user', [
                'user_id' => $user->id,
                'profile_id' => $profile->id,
            ]);

            if (isset($input['photo']) && $input['photo'] instanceof UploadedFile) {
                app(CreatePhoto::class)->execute($profile, $input['photo']);
            }

            return $user;
        });
    }
}
