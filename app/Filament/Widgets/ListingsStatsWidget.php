<?php

namespace App\Filament\Widgets;

use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Models\Experience;
use App\Models\Listing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListingsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            Stat::make(__('widgets.pending_items'),
                Listing::query()->where('status', ListingStatus::PENDING)->count() +
                Experience::query()->where('status', ExperienceStatus::PENDING)->count())
                ->description(__('widgets.awaiting_review'))
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('warning'),

            Stat::make(__('widgets.cancelled_items'),
                Listing::query()->whereIn('status', [ListingStatus::SUSPENDED, ListingStatus::ARCHIVED])->count() +
                Experience::query()->whereIn('status', [ExperienceStatus::CANCELLED, ExperienceStatus::SUSPENDED])->count())
                ->description(__('widgets.cancelled_or_suspended'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart([3, 5, 4, 3, 6, 3, 5, 4])
                ->color('danger'),

            Stat::make(__('widgets.draft_items'),
                Listing::query()->where('status', ListingStatus::DRAFT)->count() +
                Experience::query()->where('status', ExperienceStatus::DRAFT)->count())
                ->description(__('widgets.not_published_yet'))
                ->descriptionIcon('heroicon-m-document')
                ->chart([8, 3, 4, 5, 6, 3, 5, 3])
                ->color('gray'),

            Stat::make(__('widgets.rejected_items'),
                Listing::query()->where('status', ListingStatus::REJECTED)->count() +
                Experience::query()->where('status', ExperienceStatus::REJECTED)->count())
                ->description(__('widgets.did_not_meet_requirements'))
                ->descriptionIcon('heroicon-m-x-mark')
                ->chart([4, 5, 6, 5, 4, 3, 5, 4])
                ->color('danger'),
        ];
    }
}
