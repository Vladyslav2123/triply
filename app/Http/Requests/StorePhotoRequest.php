<?php

namespace App\Http\Requests;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'max:5120'], // 5MB max
            'photoable_type' => ['required', 'string', Rule::in(['profile', 'listing', 'experience'])],
            'photoable_id' => ['required', 'string', 'ulid'],
            'directory' => ['nullable', 'string'],
        ];
    }

    public function getPhotoableModel(): Model
    {
        return match ($this->input('photoable_type')) {
            'profile' => Profile::findOrFail($this->input('photoable_id')),
            'listing' => Listing::findOrFail($this->input('photoable_id')),
            'experience' => Experience::findOrFail($this->input('photoable_id')),
        };
    }
}
