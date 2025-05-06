<?php

namespace App\Actions\Photo;

use App\Constants\PhotoConstants;
use App\Models\Photo;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeletePhoto
{
    /**
     * Delete a photo from both the database and storage.
     *
     * @param  Photo  $photo  The photo to delete
     *
     * @throws RuntimeException If the photo deletion fails
     */
    public function execute(Photo $photo): void
    {
        try {
            $disk = $photo->disk;
            $url = $photo->url;

            if (($url !== PhotoConstants::DEFAULT_AVATAR_PATH) && Storage::disk($disk)->exists($url)) {
                Storage::disk($disk)->delete($url);
            }

            $photo->delete();

        } catch (Exception $e) {
            Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Failed to delete photo: '.$e->getMessage(), 500, $e);
        }
    }
}
