<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    //    protected function mutateFormDataBeforeFill(array $data): array
    //    {
    //        $data['total_price'] = $data['total_price'] ? $data['total_price']['formatted']
    //            : ($data['total_price'] ?? 0);
    //
    //        return $data;
    //    }
}
