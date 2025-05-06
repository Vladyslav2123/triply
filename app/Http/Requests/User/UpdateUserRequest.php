<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *
 *     @OA\Property(property="name", type="string", example="John"),
 *     @OA\Property(property="surname", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="newPassword123"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="newPassword123"),
 *     @OA\Property(property="phone", type="string", example="+380991234567"),
 *     @OA\Property(property="role", type="string", enum={"user", "host", "guest", "admin"}, example="user"),
 *     @OA\Property(property="is_banned", type="boolean", example=false)
 * )
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'surname' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['sometimes', 'string', Password::defaults(), 'confirmed'],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'role' => ['sometimes', new Enum(UserRole::class)],
            'is_banned' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Only admins can change roles or ban status
        if ($this->user()->role !== UserRole::ADMIN) {
            $this->request->remove('role');
            $this->request->remove('is_banned');
        }
    }
}
