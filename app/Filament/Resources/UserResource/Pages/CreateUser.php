<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\Photo\CreatePhoto;
use App\Constants\PhotoConstants;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function afterCreate(): void
    {
        $profile = $this->record->getOrCreateProfile();
        $data = $this->data;

        if (isset($data['profile_photo'])) {
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // $data['location'] = Location::fromArray($data['location']);

        return $data;
    }
}
