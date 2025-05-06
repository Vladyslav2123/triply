<?php

namespace App\Filament\Resources\ListingResource\Pages;

use App\Filament\Resources\ListingResource;
use Cknow\Money\Money;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['price_per_night'] = new Money($data['price_per_night']['amount'], $data['price_per_night']['currency']);

        return $data;
    }
}
