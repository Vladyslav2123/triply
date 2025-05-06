<?php

namespace App\Actions\Favorite;

use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreFavorite
{
    /**
     * @throws ValidationException
     */
    public function __invoke(StoreFavoriteRequest $request): Favorite
    {
        $validated = $request->validatedWithDefaults();

        $model = $this->findModel($validated['favoriteable_type'], $validated['favoriteable_id']);

        return Favorite::updateOrCreate([
            'user_id' => Auth::id(),
            'favoriteable_type' => $model->getMorphClass(),
            'favoriteable_id' => $model->id,
        ], [
            'added_at' => now(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    protected function findModel(string $type, string $id): Model
    {
        if (! class_exists($type)) {
            throw ValidationException::withMessages([
                'favoriteable_type' => 'Invalid model type.',
            ]);
        }

        if (! is_subclass_of($type, Model::class)) {
            throw ValidationException::withMessages([
                'favoriteable_type' => 'Invalid model class.',
            ]);
        }

        $model = $type::find($id);

        if (! $model) {
            throw ValidationException::withMessages([
                'favoriteable_id' => 'Model not found.',
            ]);
        }

        return $model;
    }
}
