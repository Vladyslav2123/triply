<?php

namespace App\Actions\Fortify;

use App\Actions\Profile\UpdateProfileAction;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * The profile update action instance.
     */
    protected UpdateProfileAction $updateProfileAction;

    /**
     * Create a new action instance.
     */
    public function __construct(UpdateProfileAction $updateProfileAction)
    {
        $this->updateProfileAction = $updateProfileAction;
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        // Convert Fortify input format to profile format
        $profileData = [
            'first_name' => $input['name'],
            'email' => $input['email'],
        ];

        // Use the consolidated UpdateProfileAction
        $this->updateProfileAction->execute($user, $profileData);
    }
}
