<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'favoriteable_type' => ['required', 'string', 'in:'.implode(',', array_keys(Relation::morphMap()))],
            'favoriteable_id' => 'required|ulid',
        ];
    }

    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        if (isset($validated['favoriteable_type'])) {
            $modelClass = Relation::getMorphedModel($validated['favoriteable_type']);
            if ($modelClass) {
                $validated['favoriteable_type'] = $modelClass;
            }
        }

        return $validated;
    }
}
