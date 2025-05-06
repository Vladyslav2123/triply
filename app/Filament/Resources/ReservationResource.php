<?php

namespace App\Filament\Resources;

use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Reservation;
use Carbon\Carbon;
use DateTime;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.bookings');
    }

    public static function getModelLabel(): string
    {
        return __('reservation.reservation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('reservation.reservations');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ReservationStatus::PENDING)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('reservation.id'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('reservation.status'))
                    ->badge()
                    ->formatStateUsing(fn (ReservationStatus $state): string => __('reservation.status_'.$state->value))
                    ->color(fn (ReservationStatus $state): string => match ($state->value) {
                        'confirmed', 'paid', 'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled_by_guest', 'cancelled_by_host', 'no_show' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('guest.full_name')
                    ->label(__('reservation.guest_name'))
                    ->searchable(['name', 'surname'])
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('reservationable_title')
                    ->label(__('reservation.reservationable_title'))
                    ->getStateUsing(function ($record) {
                        $reservationable = $record->reservationable;
                        if (! $reservationable) {
                            return 'Unknown';
                        }

                        $type = class_basename($reservationable);

                        return "{$type}: {$reservationable->title}";
                    })
                    ->searchable(query: function (EloquentBuilder $query, string $search): EloquentBuilder {
                        return $query->whereHasMorph('reservationable',
                            [Listing::class, Experience::class],
                            function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                            });
                    })
                    ->icon('heroicon-o-home')
                    ->limit(30),

                Tables\Columns\TextColumn::make('check_in')
                    ->label(__('reservation.check_in'))
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label(__('reservation.check_out'))
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-arrow-right'),

                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('reservation.total_price'))
                    ->money('USD')
                    ->sortable()
                    ->icon('heroicon-o-currency-dollar')
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('reservation.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('reservation.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('reservation.status'))
                    ->options(collect(ReservationStatus::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => __('reservation.status_'.$status->value)]))
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('reservation.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('reservation.created_until')),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('check_in')
                    ->form([
                        Forms\Components\DatePicker::make('check_in_from')
                            ->label(__('reservation.check_in_from')),
                        Forms\Components\DatePicker::make('check_in_until')
                            ->label(__('reservation.check_in_until')),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query
                            ->when(
                                $data['check_in_from'],
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate('check_in', '>=', $date),
                            )
                            ->when(
                                $data['check_in_until'],
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate('check_in', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Tabs::make('Reservation')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'mt-4'])
                    ->tabs([
                        Tabs\Tab::make(__('reservation.basic_info'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('reservation.guest_details'))
                                    ->description(__('reservation.guest_details_description'))
                                    ->icon('heroicon-o-user')
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('guest_id')
                                            ->relationship('guest', 'email')
                                            ->label(__('reservation.guest'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make(__('reservation.property_details'))
                                    ->description(__('reservation.property_details_description'))
                                    ->icon('heroicon-o-home')
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('reservationable_type')
                                            ->label(__('reservation.reservationable_type'))
                                            ->options([
                                                'listing' => 'Listing',
                                                'experience' => 'Experience',
                                            ])
                                            ->required()
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(fn (Set $set) => $set('reservationable_id', null)),

                                        Forms\Components\Select::make('reservationable_id')
                                            ->label(__('reservation.reservationable'))
                                            ->columnSpan(1)
                                            ->options(function (Get $get) {
                                                $type = $get('reservationable_type');

                                                if (! $type) {
                                                    return [];
                                                }

                                                if ($type === 'listing') {
                                                    return Listing::query()
                                                        ->where('status', ListingStatus::PUBLISHED)
                                                        ->pluck('title', 'id')
                                                        ->toArray();
                                                }

                                                if ($type === 'experience') {
                                                    return Experience::query()
                                                        ->where('status', ExperienceStatus::PUBLISHED)
                                                        ->pluck('title', 'id')
                                                        ->toArray();
                                                }

                                                return [];
                                            })
                                            ->searchable()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, string $state, Set $set, Get $get) {
                                                if ($operation === 'edit' || empty($state)) {
                                                    return;
                                                }

                                                $type = $get('reservationable_type');

                                                if ($type === 'listing') {
                                                    try {
                                                        $listing = Listing::findOrFail($state);
                                                        $price = $listing->price_per_night->getAmount() / 100;
                                                        $set('total_price', $price);
                                                    } catch (Exception $e) {
                                                        // Handle error silently
                                                    }
                                                } elseif ($type === 'experience') {
                                                    try {
                                                        $experience = Experience::findOrFail($state);
                                                        $price = $experience->pricing->pricePerPerson->getAmount() / 100;
                                                        $set('total_price', $price);
                                                    } catch (Exception $e) {
                                                        // Handle error silently
                                                    }
                                                }
                                            })
                                            ->required(),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('reservation.dates_and_pricing'))
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make(__('reservation.dates'))
                                    ->description(__('reservation.dates_description'))
                                    ->icon('heroicon-o-calendar-days')
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\DatePicker::make('check_in')
                                                    ->label(__('reservation.check_in'))
                                                    ->required()
                                                    ->live()
                                                    ->minDate(fn (Get $get) => now())
                                                    ->afterStateUpdated(fn ($state, Set $set) => $set('check_out', null)),
                                                Forms\Components\DatePicker::make('check_out')
                                                    ->label(__('reservation.check_out'))
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->minDate(fn (Get $get) => $get('check_in') ? Carbon::parse($get('check_in'))->addDay() : now()->addDay())
                                                    ->afterStateUpdated(function ($operation, Get $get, Set $set) {
                                                        if ($operation === 'edit') {
                                                            return;
                                                        }
                                                        $in = new DateTime($get('check_in'));
                                                        $out = new DateTime($get('check_out'));
                                                        $days = (int) $out->diff($in)->days ?: 1;

                                                        $type = $get('reservationable_type');
                                                        $reservationableId = $get('reservationable_id');

                                                        if ($type === 'listing' && $reservationableId) {
                                                            try {
                                                                $listing = Listing::findOrFail($reservationableId);
                                                                $pricePerNight = $listing->price_per_night->getAmount() / 100;
                                                                $set('total_price', $pricePerNight * $days);
                                                            } catch (Exception $e) {
                                                                // If we can't find the listing, use the current price
                                                                $price = (float) ($get('total_price')) * $days;
                                                                $set('total_price', $price);
                                                            }
                                                        } elseif ($type === 'experience' && $reservationableId) {
                                                            try {
                                                                $experience = Experience::findOrFail($reservationableId);
                                                                $pricePerPerson = $experience->pricing->pricePerPerson->getAmount() / 100;
                                                                $set('total_price', $pricePerPerson * $days);
                                                            } catch (Exception $e) {
                                                                $price = (float) ($get('total_price')) * $days;
                                                                $set('total_price', $price);
                                                            }
                                                        } else {
                                                            $price = (float) ($get('total_price')) * $days;
                                                            $set('total_price', $price);
                                                        }
                                                    }),
                                            ])
                                            ->columns([
                                                'default' => 1,
                                                'md' => 2,
                                                'xl' => 2,
                                            ]),
                                    ]),

                                Section::make(__('reservation.pricing'))
                                    ->description(__('reservation.pricing_description'))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_price.formatted')
                                            ->label(__('reservation.total_price'))
                                            ->helperText(__('reservation.total_price_helper'))
                                            ->columnSpanFull()
                                            ->readOnly(),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('reservation.status_tab'))
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Section::make(__('reservation.status_section'))
                                    ->description(__('reservation.status_description'))
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label(__('reservation.status'))
                                            ->options(collect(ReservationStatus::cases())
                                                ->mapWithKeys(fn ($status) => [$status->value => __('reservation.status_'.$status->value)]))
                                            ->required()
                                            ->searchable()
                                            ->columnSpanFull()
                                            ->native(false),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()->with(['reservationable', 'guest']);
    }
}
