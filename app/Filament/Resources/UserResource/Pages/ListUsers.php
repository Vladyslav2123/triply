<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('users.user_users')),
            'guest' => Tab::make(__('users.guest_users'))
                ->query(fn ($query) => $query->where('role', 'guest')),
            'host' => Tab::make(__('users.host_users'))
                ->query(fn ($query) => $query->where('role', 'host')),
            'user' => Tab::make(__('users.users'))
                ->query(fn ($query) => $query->where('role', 'user')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
