<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Actions\Photo\CreatePhoto;
use App\Actions\Photo\DeletePhoto;
use App\Constants\PhotoConstants;
use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function afterSave(): void
    {
        // Handle photo upload if needed
        if (isset($this->data['photo_upload']) && isset($this->data['photo_changed']) && $this->data['photo_changed']) {
            // Delete existing photo if any
            if ($this->record->photo) {
                app(DeletePhoto::class)->execute($this->record->photo);
            }

            // Create new photo from the uploaded file
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
