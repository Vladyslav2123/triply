<?php

namespace App\Filament\Widgets;

use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MainStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Отримуємо загальну суму бронювань за останній місяць
        $monthlyRevenue = Reservation::where('status', ReservationStatus::COMPLETED)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('total_price');

        // Відсоток суперхостів
        $totalHosts = Profile::count();
        $superhosts = Profile::where('is_superhost', true)->count();
        $superhostPercentage = $totalHosts > 0
            ? round(($superhosts / $totalHosts) * 100, 1)
            : 0;

        return [
            Stat::make(__('widgets.active_listings'),
                Listing::where('status', ListingStatus::PUBLISHED)->count() +
                Experience::where('status', ExperienceStatus::PUBLISHED)->count())
                ->description(__('widgets.published_listings_experiences'))
                ->descriptionIcon('heroicon-m-home')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            Stat::make(__('widgets.featured_items'),
                Listing::where('is_featured', true)->count() +
                Experience::where('is_featured', true)->count())
                ->description(__('widgets.featured_listings_experiences'))
                ->descriptionIcon('heroicon-m-star')
                ->chart([3, 5, 4, 3, 6, 3, 5, 4])
                ->color('warning'),

            Stat::make(__('widgets.users'), User::count())
                ->description(__('widgets.superhosts_percentage', ['percentage' => $superhostPercentage]))
                ->descriptionIcon('heroicon-m-users')
                ->chart([8, 3, 4, 5, 6, 3, 5, 3])
                ->color('primary'),

            Stat::make(__('widgets.monthly_revenue'), number_format($monthlyRevenue / 100, 2).' UAH')
                ->description(__('widgets.from_completed_reservations'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([4, 5, 6, 5, 4, 3, 5, 4])
                ->color('success'),

            Stat::make(__('widgets.active_reservations'),
                Reservation::whereIn('status', [ReservationStatus::PENDING, ReservationStatus::CONFIRMED])->count())
                ->description(__('widgets.pending_confirmed'))
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([4, 5, 6, 5, 4, 3, 5, 4])
                ->color('warning'),
        ];
    }

    private function calculateAverageRating(): string
    {
        $averageRating = DB::table('reviews')
            ->whereNotNull('overall_rating')
            ->avg('overall_rating');

        return number_format($averageRating ?? 0, 1);
    }
}
