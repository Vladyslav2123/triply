<?php

namespace App\Filament\Resources;

use App\Enums\Amenity;
use App\Enums\ListingType;
use App\Enums\NoticeType;
use App\Enums\PropertyType;
use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function getPluralModelLabel(): string
    {
        return __('listing.plural');
    }

    public static function getModelLabel(): string
    {
        return __('listing.singular');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.title'))->aside()->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('listing.title'))
                            ->maxLength(32)
                            ->required()
                            ->hint(__('listing.short_title'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set) {
                                if ($operation === 'edit' && $state === null) {
                                    return;
                                }
                                $slug = Str::slug($state).'-'.Str::random(5);
                                $set('slug', $slug);
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('listing.slug'))
                            ->readOnly(),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.desc'))->aside()->schema([
                        Forms\Components\Tabs::make('Tabs')->tabs([
                            Forms\Components\Tabs\Tab::make(__('listing.list_desc'))->schema([
                                Forms\Components\Textarea::make('description.listing_description')
                                    ->label(__('listing.list_desc'))
                                    ->minLength(2)
                                    ->maxLength(1024)
                                    ->autosize(true)
                                    ->hint(__('listing.brief_desc')),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.your_prop'))->schema([
                                Forms\Components\Textarea::make('description.your_property')
                                    ->label(__('listing.your_prop'))
                                    ->minLength(2)
                                    ->maxLength(1024)
                                    ->autosize(true)
                                    ->hint(__('listing.prop_det')),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.guest_acc'))->schema([
                                Forms\Components\Textarea::make('description.guest_access')
                                    ->label(__('listing.guest_acc'))
                                    ->minLength(2)
                                    ->maxLength(1024)
                                    ->autosize(true),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.guest_int'))->schema([
                                Forms\Components\Textarea::make('description.interaction_with_guests')
                                    ->label(__('listing.guest_int'))
                                    ->minLength(2)
                                    ->maxLength(1024)
                                    ->autosize(true),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.other_det'))->schema([
                                Forms\Components\Textarea::make('description.other_details')
                                    ->label(__('listing.other_det'))
                                    ->minLength(2)
                                    ->maxLength(1024)
                                    ->autosize(true),
                            ]),
                        ])
                            ->columns(2),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.prop_type'))->aside()->schema([
                        Forms\Components\Select::make('host_id')
                            ->label(__('listing.host'))
                            ->relationship('host', 'name')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label(__('listing.type'))
                            ->placeholder(__('listing.place_like'))
                            ->options(self::getEnumOptions(PropertyType::class))
                            ->required()
                            ->preload()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('subtype', null)),
                        Forms\Components\Select::make('subtype')
                            ->label(__('listing.subtype'))
                            ->options(
                                fn (Forms\Get $get) => $get('type')
                                    ? collect(PropertyType::from($get('type'))
                                        ->getSubtypes())
                                        ->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', $value))])
                                        ->toArray() : []
                            )
                            ->default('house')
                            ->preload()
                            ->placeholder(__('listing.prop_type'))
                            ->disabled(fn (Forms\Get $get) => empty($get('type')))
                            ->required(),
                        Forms\Components\Select::make('listing_type')
                            ->label(__('listing.list_type'))
                            ->placeholder(__('listing.list_type'))
                            ->options(self::getEnumOptions(ListingType::class))
                            ->required(),
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('rooms_rules.floors_count')
                                ->label(__('listing.floors'))
                                ->minValue(1)
                                ->maxValue(100)
                                ->numeric()
                                ->inputMode('integer')
                                ->placeholder(__('listing.how_many_floors')),
                            Forms\Components\TextInput::make('rooms_rules.floor_listing')
                                ->label(__('listing.floor_num'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->inputMode('integer')
                                ->placeholder(__('listing.which_floor')),
                            Forms\Components\TextInput::make('rooms_rules.year_built')
                                ->label(__('listing.year_built'))
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue(2025)
                                ->inputMode('integer')
                                ->placeholder(__('listing.year_built_hint')),
                            Forms\Components\TextInput::make('rooms_rules.property_size')
                                ->label(__('listing.prop_size'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(1000)
                                ->inputMode('decimal')
                                ->placeholder(__('listing.size_hint')),
                        ]),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.acc_features'))->aside()->schema([
                        Forms\Components\Toggle::make('accessibility_features.disabled_parking_spot')
                            ->label(__('listing.dis_park')),
                        Forms\Components\Toggle::make('accessibility_features.guest_entrance')
                            ->label(__('listing.guest_ent')),
                        Forms\Components\Toggle::make('accessibility_features.step_free_access')
                            ->label(__('listing.step_free')),
                        Forms\Components\Toggle::make('accessibility_features.swimming_pool')
                            ->label(__('listing.swim_pool')),
                        Forms\Components\Toggle::make('accessibility_features.ceiling_hoist')
                            ->label(__('listing.ceil_hoist')),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('Location'))->aside()->schema([
                        Forms\Components\TextInput::make('location.address.city')
                            ->label(__('users.city'))
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('location.address.street')
                            ->label(__('users.street'))
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('location.address.country')
                            ->label(__('users.country'))
                            ->string()
                            ->required(),
                        Forms\Components\TextInput::make('location.address.postal_code')
                            ->label(__('users.postal_code'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('location.coordinates.latitude')
                            ->label(__('users.latitude'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('location.coordinates.longitude')
                            ->label(__('users.longitude'))
                            ->numeric()
                            ->required(),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.amen'))->description(__('listing.amen_added'))->aside()->schema([
                        Forms\Components\Tabs::make(__('listing.amen'))->tabs([
                            Forms\Components\Tabs\Tab::make(__('listing.basics'))->schema([
                                Forms\Components\Select::make('amenities.basics')
                                    ->label(__('listing.basics'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::BASICS->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.bath'))->schema([
                                Forms\Components\Select::make('amenities.bathroom')
                                    ->label(__('listing.bath'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::BATHROOM->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.bed_laun'))->schema([
                                Forms\Components\Select::make('amenities.bedroom_and_laundry')
                                    ->label(__('listing.bed_laun'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::BEDROOM_LAUNDRY->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.entertain'))->schema([
                                Forms\Components\Select::make('amenities.entertainment')
                                    ->label(__('listing.entertain'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::ENTERTAINMENT->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.family'))->schema([
                                Forms\Components\Select::make('amenities.family')
                                    ->label(__('listing.family'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::FAMILY->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.heat_cool'))->schema([
                                Forms\Components\Select::make('amenities.heating_and_cooling')
                                    ->label(__('listing.heat_cool'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::HEATING_COOLING->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.home_safe'))->schema([
                                Forms\Components\Select::make('amenities.home_safety')
                                    ->label(__('listing.home_safe'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::HOME_SAFETY->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.int_office'))->schema([
                                Forms\Components\Select::make('amenities.internet_and_office')
                                    ->label(__('listing.int_office'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::INTERNET_OFFICE->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.kit_din'))->schema([
                                Forms\Components\Select::make('amenities.kitchen_and_dining')
                                    ->label(__('listing.kit_din'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::KITCHEN_DINING->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.loc_feat'))->schema([
                                Forms\Components\Select::make('amenities.location_features')
                                    ->label(__('listing.loc_feat'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::LOCATION_FEATURES->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.outdoor'))->schema([
                                Forms\Components\Select::make('amenities.outdoor')
                                    ->label(__('listing.outdoor'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::OUTDOOR->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.park_fac'))->schema([
                                Forms\Components\Select::make('amenities.parking_and_facilities')
                                    ->label(__('listing.park_fac'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::PARKING_FACILITIES->getSubtypes())),
                            ]),
                            Forms\Components\Tabs\Tab::make(__('listing.services'))->schema([
                                Forms\Components\Select::make('amenities.services')
                                    ->label(__('listing.services'))
                                    ->multiple()
                                    ->options(self::getSubtypeEnum(Amenity::SERVICES->getSubtypes())),
                            ]),
                        ]),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.price_det'))->description(__('listing.night_set'))->aside()->schema([
                        Forms\Components\TextInput::make('price_per_night.amount')
                            ->label(__('listing.night_price'))
                            ->numeric()
                            ->inputMode('integer')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, Set $set) => $set('price_per_night.formatted', $state)),
                        Forms\Components\TextInput::make('price_per_night.currency')
                            ->label(__('listing.curr')),
                        Forms\Components\TextInput::make('price_per_night.formatted')
                            ->label(__('listing.form_price'))
                            ->readOnly(),
                        Forms\Components\Section::make(__('listing.discounts'))->schema([
                            Forms\Components\TextInput::make('discounts.weekly')
                                ->label(__('listing.week_disc'))
                                ->placeholder(__('listing.week_disc'))
                                ->numeric()
                                ->inputMode('integer'),
                            Forms\Components\TextInput::make('discounts.monthly')
                                ->label(__('listing.month_disc'))
                                ->placeholder(__('listing.month_disc'))
                                ->numeric()
                                ->inputMode('integer'),
                        ]),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.avail'))->description(__('listing.night_set'))->aside()->schema([
                        Forms\Components\TextInput::make('availability_settings.min_stay')
                            ->label(__('listing.min_nights'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\TextInput::make('availability_settings.max_stay')
                            ->label(__('listing.max_nights'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(364)
                            ->required(),
                        Forms\Components\Select::make('advance_notice_type')
                            ->label(__('listing.adv_notice'))
                            ->placeholder(__('listing.adv_notice'))
                            ->options(self::getEnumOptions(NoticeType::class))
                            ->required(),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.guest_safe'))->description(__('listing.safe_det'))->aside()->schema([
                        Forms\Components\Toggle::make('guest_safety.smoke_detector')
                            ->label(__('listing.smoke_alarm')),
                        Forms\Components\Toggle::make('guest_safety.fire_extinguisher')
                            ->label(__('listing.co_alarm')),
                        Forms\Components\Toggle::make('guest_safety.security_camera')
                            ->label(__('listing.sec_cam')),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.house_rules'))->description(__('listing.rule_exp'))->aside()->schema([
                        Forms\Components\Toggle::make('house_rules.pets_allowed')
                            ->label(__('listing.pets')),
                        Forms\Components\Toggle::make('house_rules.events_allowed')
                            ->label(__('listing.events')),
                        Forms\Components\Toggle::make('house_rules.smoking_allowed')
                            ->label(__('listing.smoking')),
                        Forms\Components\Toggle::make('house_rules.quiet_hours')
                            ->label(__('listing.quiet_hrs')),
                        Forms\Components\Toggle::make('house_rules.commercial_photography_allowed')
                            ->label(__('listing.comm_photo')),
                        Forms\Components\TextInput::make('house_rules.number_of_guests')
                            ->label(__('listing.num_guests'))
                            ->minValue(1)
                            ->maxValue(100)
                            ->numeric()
                            ->inputMode('integer'),
                        Forms\Components\TextInput::make('house_rules.additional_rules')
                            ->label(__('listing.add_rules'))
                            ->placeholder(__('listing.share_exp')),
                    ]),
                ]),

                Forms\Components\Section::make()->schema([
                    Forms\Components\Section::make(__('listing.accept_guests'))->description(__('listing.accept_guests'))->aside()->schema([
                        Forms\Components\Toggle::make('accept_guests.adults')
                            ->label(__('listing.adults')),
                        Forms\Components\Toggle::make('accept_guests.children')
                            ->label(__('listing.children')),
                        Forms\Components\Toggle::make('accept_guests.pets')
                            ->label(__('listing.pets')),
                    ]),
                ]),
            ]);
    }

    private static function getEnumOptions($enum): array
    {
        $enumCollect = collect($enum::all())
            ->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', $value))])
            ->toArray();

        return $enumCollect;
    }

    private static function getSubtypeEnum(array $subType): array
    {
        $subEnum = collect($subType)
            ->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', $value))])
            ->toArray();

        return $subEnum;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Listing $record) => self::getUrl('view', ['record' => $record->slug]))
            ->columns([
                TextColumn::make('title')->label(__('listing.title'))->searchable(),
                TextColumn::make('price_per_night')->label(__('listing.night_price'))->numeric()->sortable(),
                TextColumn::make('type')->label(__('listing.type'))->sortable(),
                TextColumn::make('subtype')->label(__('listing.subtype'))->searchable(),
                TextColumn::make('listing_type')->label(__('listing.list_type')),
                TextColumn::make('host.full_name')->label(__('listing.host_name'))->searchable(['name', 'surname']),
                TextColumn::make('created_at')->label(__('listing.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('listing.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'view' => Pages\ViewListing::route('/{record:slug}'),
            'edit' => Pages\EditListing::route('/{record:slug}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()->with(['host', 'photos']);
    }
}
