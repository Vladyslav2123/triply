<?php

namespace App\Actions\Favorite;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;

class DestroyFavorite
{
    public function __invoke(Favorite $favorite): JsonResponse
    {
        $favorite->delete();

        return response()->json([
            'message' => 'Removed from favorites.',
        ]);
    }
}
