<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\Photo\CreatePhoto;
use App\Actions\Photo\DeletePhoto;
use App\Constants\PhotoConstants;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function beforeSave(): void
    {
        //
    }

    public function afterSave(): void
    {
        $profile = $this->record->getOrCreateProfile();
        $data = $this->data;

        if (isset($data['profile_photo_changed']) && $data['profile_photo_changed'] && isset($data['profile_photo'])) {
            if ($profile->photo) {
                app(DeletePhoto::class)->execute($profile->photo);
            }

            $uploadedFile = Storage::disk('s3')->get($data['profile_photo']);
            if ($uploadedFile) {
                $tempFile = tempnam(sys_get_temp_dir(), 'profile_photo');
                file_put_contents($tempFile, $uploadedFile);

                $file = new UploadedFile(
                    $tempFile,
                    basename($data['profile_photo']),
                    Storage::disk('s3')->mimeType($data['profile_photo']),
                    null,
                    true
                );

                app(CreatePhoto::class)->execute($profile, $file, PhotoConstants::DIRECTORY_USERS);

                @unlink($tempFile);
            }
        }
    }

    protected function mutateProfileData(array $profileData): array
    {
        // We don't need to transform the values as they are already in the correct format
        // The enum values are already stored as strings like 'turkish', 'french', etc.
        // The transformation was causing the validation error

        return $profileData;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Process profile data if it exists
        if (isset($data['profile'])) {
            // We don't need to transform the values anymore
            // Just pass them through as they are already in the correct format
        }

        return $data;
    }
}
