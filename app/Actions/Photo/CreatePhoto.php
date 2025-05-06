<?php

namespace App\Actions\Photo;

use App\Constants\PhotoConstants;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

class CreatePhoto
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 's3');
    }

    public function execute(Model $model, ?UploadedFile $file = null, ?string $directory = null): Photo
    {
        if ($file) {
            return $this->createFromFile($model, $file, $directory);
        }

        return $this->createDefaultAvatar($model);
    }

    private function createFromFile(Model $model, UploadedFile $file, ?string $directory = null): Photo
    {
        $directory = $directory ?? $this->getDefaultDirectory($model);
        $filename = Str::random(40).'.'.$file->getClientOriginalExtension();

        $width = null;
        $height = null;

        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $imageSize = getimagesize($file->getPathname());
                if ($imageSize) {
                    [$width, $height] = $imageSize;
                }

                $manager = new ImageManager('gd');
                $img = $manager->read($file->getPathname());

                $img = $this->resizeImageToConsistentDimensions($img);

                $width = $img->width();
                $height = $img->height();

                $processedImageContent = $img->encode()->toString();
                $path = $this->buildPath($directory, $filename);

                if (Storage::disk($this->disk)->put($path, $processedImageContent, 'public')) {
                    Log::info('Image processed and stored successfully', [
                        'path' => $path,
                        'width' => $width,
                        'height' => $height,
                    ]);
                } else {
                    Log::error('Failed to store processed image', ['path' => $path]);
                    $path = $file->storeAs($directory, $filename, [
                        'disk' => $this->disk,
                        'visibility' => 'public',
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Error processing image', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $path = $file->storeAs($directory, $filename, [
                    'disk' => $this->disk,
                    'visibility' => 'public',
                ]);
            }
        } else {
            $path = $file->storeAs($directory, $filename, [
                'disk' => $this->disk,
                'visibility' => 'public',
            ]);
        }

        $photoData = [
            'url' => $path,
            'disk' => $this->disk,
            'directory' => $directory,
            'size' => $file->getSize(),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'width' => $width,
            'height' => $height,
            'uploaded_at' => now(),
        ];

        return match (true) {
            method_exists($model, 'photos') && $model->photos() instanceof MorphMany => $model->photos()->create($photoData),
            method_exists($model, 'photo') && $model->photo() instanceof MorphOne => $model->photo()->create($photoData),
            default => throw new RuntimeException('Model must have either photos() or photo() relationship defined'),
        };
    }

    private function getDefaultDirectory(Model $model): string
    {
        return match ($model::class) {
            User::class => PhotoConstants::DIRECTORY_USERS,
            Profile::class => PhotoConstants::DIRECTORY_USERS,
            Experience::class => PhotoConstants::DIRECTORY_EXPERIENCES,
            Listing::class => PhotoConstants::DIRECTORY_LISTINGS,
            default => PhotoConstants::DIRECTORY_PHOTOS,
        };
    }

    /**
     * Resize image to consistent dimensions
     */
    private function resizeImageToConsistentDimensions(ImageInterface $image): ImageInterface
    {
        $maxWidth = 1200;
        $maxHeight = 800;

        $width = $image->width();
        $height = $image->height();

        if ($width > $maxWidth || $height > $maxHeight) {
            $aspectRatio = $width / $height;

            if ($width > $height) {
                $newWidth = min($width, $maxWidth);
                $newHeight = (int) ($newWidth / $aspectRatio);

                if ($newHeight > $maxHeight) {
                    $newHeight = $maxHeight;
                    $newWidth = (int) ($newHeight * $aspectRatio);
                }
            } else {
                $newHeight = min($height, $maxHeight);
                $newWidth = (int) ($newHeight * $aspectRatio);

                if ($newWidth > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = (int) ($newWidth / $aspectRatio);
                }
            }

            $image = $image->resize($newWidth, $newHeight);
        }

        return $image;
    }

    /**
     * Build a consistent path for storing files.
     */
    private function buildPath(string $directory, string $filename): string
    {
        return $directory.'/'.$filename;
    }

    private function createDefaultAvatar(Model $model): Photo
    {
        $defaultPath = PhotoConstants::DEFAULT_AVATAR_PATH;

        $photoData = [
            'url' => $defaultPath,
            'disk' => $this->disk,
            'directory' => PhotoConstants::DIRECTORY_PHOTOS,
            'size' => 12345,
            'original_filename' => 'default-avatar.png',
            'mime_type' => 'image/png',
            'width' => 512,
            'height' => 512,
            'uploaded_at' => now(),
        ];

        return match (true) {
            method_exists($model, 'photos') && $model->photos() instanceof MorphMany => $model->photos()->create($photoData),
            method_exists($model, 'photo') && $model->photo() instanceof MorphOne => $model->photo()->create($photoData),
            default => throw new RuntimeException('Model must have either photos() or photo() relationship defined'),
        };
    }
}
