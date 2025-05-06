<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Actions\Photo\CreatePhoto;
use App\Constants\PhotoConstants;
use App\Filament\Resources\ProfileResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure we have a user_id
        if (! isset($data['user_id'])) {
            // This would typically be handled by a relationship selector in the form
            // For now, we'll just show an error
            $this->halt('A user must be selected for this profile');
        }

        return $data;
    }

    public function afterCreate(): void
    {
        // Handle photo upload if needed
        if (isset($this->data['photo_upload']) && isset($this->data['photo_changed']) && $this->data['photo_changed']) {
            $uploadedFile = Storage::disk('s3')->get($this->data['photo_upload']);
            if ($uploadedFile) {
                $tempFile = tempnam(sys_get_temp_dir(), 'profile_photo');
                file_put_contents($tempFile, $uploadedFile);

                $file = new \Illuminate\Http\UploadedFile(
                    $tempFile,
                    basename($this->data['photo_upload']),
                    Storage::disk('s3')->mimeType($this->data['photo_upload']),
                    null,
                    true
                );

                app(CreatePhoto::class)->execute($this->record, $file, PhotoConstants::DIRECTORY_USERS);

                // Clean up the temporary file
                @unlink($tempFile);
            }
        }
    }
}
