<?php

namespace App\Filament\Resources;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.users');
    }

    public static function getModelLabel(): string
    {
        return __('users.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.users');
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (User $record) => UserResource::getUrl('view', ['record' => $record->slug]))
            ->columns(self::getTableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label(__('users.role'))
                    ->options(UserRole::options())
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('email_verified')
                    ->label(__('users.email_verified'))
                    ->query(fn (EloquentBuilder $query) => $query->whereNotNull('email_verified_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_birth_date')
                    ->label(__('users.has_birth_date'))
                    ->query(fn (EloquentBuilder $query) => $query->whereHas('profile', fn ($q) => $q->whereNotNull('birth_date')))
                    ->toggle(),

                // Група фільтрів для дат
                Tables\Filters\Filter::make('created_this_month')
                    ->label(__('users.registered_this_month'))
                    ->query(fn (EloquentBuilder $query) => $query->whereMonth('created_at', now()->month))
                    ->toggle(),

                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('users.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('users.created_until')),
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

                // Група фільтрів для профілю
                Tables\Filters\SelectFilter::make('gender')
                    ->label(__('users.gender'))
                    // ->relationship('profile', 'gender')
                    ->options(Gender::options())
                    ->query(function (EloquentBuilder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereHas('profile', function ($profileQuery) use ($data) {
                            return $profileQuery->whereIn('gender', $data['values']);
                        });
                    })
                    ->preload(),

                Tables\Filters\Filter::make('is_superhost')
                    ->label(__('users.is_superhost'))
                    ->query(fn (EloquentBuilder $query) => $query->whereHas('profile', fn ($q) => $q->where('is_superhost', true)))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('education_level')
                    ->label(__('users.education_level'))
                    // ->relationship('profile', 'education_level')
                    ->options(EducationLevel::options())
                    ->query(function (EloquentBuilder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereHas('profile', function ($profileQuery) use ($data) {
                            return $profileQuery->whereIn('education_level', $data['values']);
                        });
                    })
                    ->preload(),

                Tables\Filters\SelectFilter::make('location_country')
                    ->label(__('users.location_country'))
                    ->options([
                        'Україна' => 'Україна',
                        'Польща' => 'Польща',
                        'Німеччина' => 'Німеччина',
                        'США' => 'США',
                        'Великобританія' => 'Великобританія',
                    ])
                    ->query(function (EloquentBuilder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereHas('profile', function ($profileQuery) use ($data) {
                            return $profileQuery->whereRaw("location->'address'->>'country' IN ('".implode("','", $data['values'])."')");
                        });
                    }),

                // Група фільтрів для мов та інтересів
                Tables\Filters\SelectFilter::make('languages')
                    ->label(__('users.languages'))
                    ->options(Language::options())
                    ->multiple()
                    ->query(function (EloquentBuilder $query, array $data) {
                        $values = $data['values'] ?? null;
                        if (blank($values)) {
                            return $query;
                        }

                        return $query->whereHas('profile', function ($profileQuery) use ($values) {

                            foreach ($values as $value) {
                                $profileQuery->whereJsonContains('languages', $value);
                            }

                            return $profileQuery;
                        });
                    })
                    ->preload(),

                Tables\Filters\SelectFilter::make('interests')
                    ->label(__('users.interests'))
                    ->options(Interest::options())
                    ->multiple()
                    ->query(function (EloquentBuilder $query, array $data) {
                        $values = $data['values'] ?? null;
                        if (blank($values)) {
                            return $query;
                        }

                        return $query->whereHas('profile', function ($profileQuery) use ($values) {
                            foreach ($values as $value) {
                                $profileQuery->whereJsonContains('interests', $value);
                            }

                            return $profileQuery;
                        });
                    })
                    ->searchable()
                    ->preload(),

                // Група фільтрів для активності
                Tables\Filters\Filter::make('has_listings')
                    ->label(__('users.has_listings'))
                    ->query(fn (EloquentBuilder $query) => $query->whereHas('listings'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_reservations')
                    ->label(__('users.has_reservations'))
                    ->query(fn (EloquentBuilder $query) => $query->whereHas('reservations'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_reviews')
                    ->label(__('users.has_reviews'))
                    ->query(fn (EloquentBuilder $query) => $query->whereHas('reviews'))
                    ->toggle(),
            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)
            ->filtersFormSchema(fn (array $filters): array => [
                Forms\Components\Section::make(__('users.basic_info'))
                    ->description(__('users.filter_by_basic_info'))
                    ->schema([
                        $filters['role'],
                        $filters['email_verified'],
                        $filters['has_birth_date'],
                    ])
                    ->columns(3),

                Forms\Components\Section::make(__('users.date_filters'))
                    ->description(__('users.filter_by_date'))
                    ->schema([
                        $filters['created_this_month'],
                        $filters['created_from'],
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('users.profile_filters'))
                    ->description(__('users.filter_by_profile'))
                    ->schema([
                        $filters['gender'],
                        $filters['is_superhost'],
                        $filters['education_level'],
                        $filters['location_country'],
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('users.preferences_filters'))
                    ->description(__('users.filter_by_preferences'))
                    ->schema([
                        $filters['languages'],
                        $filters['interests'],
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('users.activity_filters'))
                    ->description(__('users.filter_by_activity'))
                    ->schema([
                        $filters['has_listings'],
                        $filters['has_reservations'],
                        $filters['has_reviews'],
                    ])
                    ->columns(3),
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

    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\Layout\Split::make([
                Tables\Columns\ImageColumn::make('profile.photo.url')
                    ->label(__('users.photo'))
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => $record->profile?->photo?->url ?? Storage::url('users/default.png'))
                    ->grow(false),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('profile.full_name')
                        ->label(__('users.name'))
                        ->searchable(query: function (EloquentBuilder $query, string $search): EloquentBuilder {
                            return $query->whereHas('profile', function ($profileQuery) use ($search) {
                                return $profileQuery->where('first_name', 'LIKE', "%{$search}%")
                                    ->orWhere('last_name', 'LIKE', "%{$search}%");
                            });
                        })
                        ->sortable(query: function (EloquentBuilder $query, string $direction): EloquentBuilder {
                            return $query->orderBy(function ($query) {
                                $query->selectRaw("CONCAT(profiles.first_name, ' ', profiles.last_name)")
                                    ->from('profiles')
                                    ->whereColumn('profiles.user_id', 'users.id')
                                    ->whereNull('profiles.deleted_at')
                                    ->limit(1);
                            }, $direction);
                        })
                        ->weight('bold'),
                    Tables\Columns\TextColumn::make('email')
                        ->label(__('users.email'))
                        ->searchable()
                        ->sortable()
                        ->color('gray')
                        ->size('sm'),
                ]),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('users.role'))
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->color(fn (UserRole $state): string => match ($state) {
                        UserRole::ADMIN => 'danger',
                        UserRole::HOST => 'warning',
                        UserRole::USER => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('profile.address_formatted')
                    ->label(__('users.location'))
                    ->icon('heroicon-m-map-pin')
                    ->searchable(query: function (EloquentBuilder $query, string $search): EloquentBuilder {
                        $search = strtolower($search);

                        return $query->whereHas('profile', function ($profileQuery) use ($search) {
                            return $profileQuery->where(function ($q) use ($search) {
                                $q->whereRaw("location->>'address' IS NOT NULL")
                                    ->whereRaw("location->'address'->>'country' ILIKE ?", ['%'.$search.'%'])
                                    ->orWhereRaw("location->'address'->>'city' ILIKE ?", ['%'.$search.'%'])
                                    ->orWhereRaw("location->'address'->>'street' ILIKE ?", ['%'.$search.'%']);
                            });
                        });
                    }),
                Tables\Columns\TextColumn::make('profile.is_superhost')
                    ->label(__('users.is_superhost'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('users.yes') : __('users.no'))
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable()
                    ->visibleFrom('lg')
                    ->icon('heroicon-m-user')
                    ->grow(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->visible(false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->visible(false),
            ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('User')
                    ->tabs([
                        Tabs\Tab::make(__('users.basic_info'))
                            ->schema([
                                self::getUserInfoSection(),
                            ]),
                        Tabs\Tab::make(__('users.profile'))
                            ->schema([
                                self::getUserProfileDetails(),
                                self::getUserLocationSection(),
                                self::getUserSocialSection(),
                                self::getUserPersonalSection(),
                                self::getUserTravelSection(),
                                self::getUserWorkSection(),
                                self::getUserBioSection(),
                            ]),
                        Tabs\Tab::make(__('users.security'))
                            ->schema([
                                self::getSecuritySection(),
                            ]),
                        Tabs\Tab::make(__('users.notifications'))
                            ->schema([
                                self::getNotificationPreferencesSection(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
            ]);
    }

    private static function getUserInfoSection(): Section
    {
        return Section::make(__('users.userInfo'))
            ->description(__('users.userInfo_description'))
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('profile.first_name')
                            ->label(__('users.first_name'))
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\TextInput::make('profile.last_name')
                            ->label(__('users.last_name'))
                            ->maxLength(100)
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label(__('users.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\Select::make('role')
                            ->label(__('users.role'))
                            ->options(UserRole::options())
                            ->enum(UserRole::class)
                            ->required()
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label(__('users.phone'))
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\DatePicker::make('profile.birth_date')
                            ->label(__('users.birth_date'))
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('users.slug'))
                            ->maxLength(255)
                            ->disabled()
                            ->helperText(__('users.slug_helper'))
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\Toggle::make('is_banned')
                            ->label(__('users.is_banned'))
                            ->helperText(__('users.is_banned_helper'))
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getUserProfileDetails(): Section
    {
        return Section::make(__('users.profile_details'))
            ->relationship('profile')
            ->schema([
                Forms\Components\Select::make('gender')
                    ->label(__('users.gender'))
                    ->options(Gender::options())
                    ->enum(Gender::class),

                Forms\Components\Toggle::make('is_superhost')
                    ->label(__('users.is_superhost')),

                Forms\Components\TextInput::make('response_speed')
                    ->label(__('users.response_speed'))
                    ->numeric()
                    ->suffix('%')
                    ->maxValue(100),
                Forms\Components\TextInput::make('rating')
                    ->label(__('users.rating'))
                    ->numeric()
                    ->suffix('out of 5')
                    ->maxValue(5),
                Forms\Components\TextInput::make('reviews_count')
                    ->label(__('users.reviews_count'))
                    ->numeric(),
                Forms\Components\TextInput::make('views_count')
                    ->label(__('users.views_count'))
                    ->numeric(),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label(__('users.is_verified'))
                            ->disabled(),
                        Forms\Components\TextInput::make('verification_method')
                            ->label(__('users.verification_method'))
                            ->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label(__('users.verified_at'))
                    ->disabled(),
                Forms\Components\DateTimePicker::make('last_active_at')
                    ->label(__('users.last_active_at'))
                    ->disabled(),
            ]);
    }

    private static function getUserLocationSection(): Section
    {
        return Section::make(__('users.location'))
            ->relationship('profile')
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('location.address.street')
                            ->label(__('users.street'))
                            ->maxLength(255)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\TextInput::make('location.address.city')
                            ->label(__('users.city'))
                            ->maxLength(255)
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('location.address.postal_code')
                            ->label(__('users.postal_code'))
                            ->maxLength(20)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\TextInput::make('location.address.country')
                            ->label(__('users.country'))
                            ->maxLength(255)
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('location.coordinates.latitude')
                            ->label(__('users.latitude'))
                            ->numeric()
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\TextInput::make('location.coordinates.longitude')
                            ->label(__('users.longitude'))
                            ->numeric()
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getUserSocialSection(): Section
    {
        return Section::make(__('users.social_profiles'))
            ->relationship('profile')
            ->schema([
                Forms\Components\TextInput::make('facebook_url')
                    ->label(__('users.facebook_url'))
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('instagram_url')
                    ->label(__('users.instagram_url'))
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('twitter_url')
                    ->label(__('users.twitter_url'))
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('linkedin_url')
                    ->label(__('users.linkedin_url'))
                    ->url()
                    ->maxLength(255),
            ]);
    }

    private static function getUserPersonalSection(): Section
    {
        return Section::make(__('users.personal_details'))
            ->relationship('profile')
            ->schema([
                Forms\Components\Select::make('education_level')
                    ->label(__('users.education_level'))
                    ->options(EducationLevel::options())
                    ->enum(EducationLevel::class),
                Forms\Components\Select::make('languages')
                    ->label(__('users.languages'))
                    ->multiple()
                    ->options(Language::options()),
                Forms\Components\Select::make('interests')
                    ->label(__('users.interests'))
                    ->multiple()
                    ->options(Interest::options()),
                Forms\Components\TextInput::make('time_spent_on')
                    ->label(__('users.time_spent_on'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('useless_skill')
                    ->label(__('users.useless_skill'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('pets')
                    ->label(__('users.pets'))
                    ->maxLength(255),
                Forms\Components\Toggle::make('birth_decade')
                    ->label(__('users.birth_decade')),
                Forms\Components\TextInput::make('favorite_high_school_song')
                    ->label(__('users.favorite_high_school_song'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('fun_fact')
                    ->label(__('users.fun_fact'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('obsession')
                    ->label(__('users.obsession'))
                    ->maxLength(255),
            ]);
    }

    private static function getUserTravelSection(): Section
    {
        return Section::make(__('users.travel_preferences'))
            ->relationship('profile')
            ->schema([
                Forms\Components\TextInput::make('dream_destination')
                    ->label(__('users.dream_destination'))
                    ->maxLength(255),
                Forms\Components\TagsInput::make('next_destinations')
                    ->label(__('users.next_destinations')),
                Forms\Components\Toggle::make('travel_history')
                    ->label(__('users.travel_history')),
                Forms\Components\TextInput::make('favorite_travel_type')
                    ->label(__('users.favorite_travel_type'))
                    ->maxLength(255),
            ]);
    }

    private static function getUserWorkSection(): Section
    {
        return Section::make(__('users.work_education'))
            ->relationship('profile')
            ->schema([
                Forms\Components\TextInput::make('work')
                    ->label(__('users.work'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('job_title')
                    ->label(__('users.job_title'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('company')
                    ->label(__('users.company'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('school')
                    ->label(__('users.school'))
                    ->maxLength(255),
            ]);
    }

    private static function getUserBioSection(): Section
    {
        return Section::make(__('users.biography'))
            ->relationship('profile')
            ->schema([
                Forms\Components\TextInput::make('biography_title')
                    ->label(__('users.biography_title'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('about')
                    ->label(__('users.about'))
                    ->rows(5),
            ]);
    }

    private static function getSecuritySection(): Section
    {
        return Section::make(__('users.security_settings'))
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label(__('users.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('email_verified')
                    ->label(__('users.email_verified'))
                    ->dehydrated(false)
                    ->disabled()
                    ->default(fn (?User $record) => $record?->email_verified_at !== null),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('users.email_verified_at'))
                    ->disabled(),
                Forms\Components\TextInput::make('new_password')
                    ->label(__('users.new_password'))
                    ->password()
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->confirmed()
                    ->minLength(8)
                    ->maxLength(255),
                Forms\Components\TextInput::make('new_password_confirmation')
                    ->label(__('users.confirm_password'))
                    ->password()
                    ->dehydrated(false)
                    ->required(fn (string $context, $get): bool => $context === 'create' || filled($get('new_password'))),
                Forms\Components\Toggle::make('two_factor_enabled')
                    ->label(__('users.two_factor_enabled'))
                    ->helperText(__('users.two_factor_help'))
                    ->dehydrated(false)
                    ->disabled()
                    ->default(fn (?User $record) => $record?->two_factor_confirmed_at !== null),
                Forms\Components\DateTimePicker::make('two_factor_confirmed_at')
                    ->label(__('users.two_factor_confirmed_at'))
                    ->disabled(),
                Forms\Components\DateTimePicker::make('last_login_at')
                    ->label(__('users.last_login_at'))
                    ->disabled(),
                Forms\Components\TextInput::make('last_login_ip')
                    ->label(__('users.last_login_ip'))
                    ->disabled(),
            ]);
    }

    private static function getNotificationPreferencesSection(): Section
    {
        return Section::make(__('users.notification_preferences'))
            ->relationship('profile')
            ->schema([
                Forms\Components\Toggle::make('email_notifications')
                    ->label(__('users.email_notifications')),
                Forms\Components\Toggle::make('sms_notifications')
                    ->label(__('users.sms_notifications')),
                Forms\Components\Select::make('preferred_language')
                    ->label(__('users.preferred_language'))
                    ->options([
                        'uk' => 'Українська',
                        'en' => 'English',
                        'pl' => 'Polski',
                        'de' => 'Deutsch',
                    ])
                    ->default('uk'),
                Forms\Components\Select::make('preferred_currency')
                    ->label(__('users.preferred_currency'))
                    ->options([
                        'UAH' => 'Гривня (₴)',
                        'USD' => 'US Dollar ($)',
                        'EUR' => 'Euro (€)',
                        'PLN' => 'Złoty (zł)',
                    ])
                    ->default('UAH'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record:slug}'),
            'edit' => Pages\EditUser::route('/{record:slug}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()->with(['profile', 'profile.photo']);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Log::info('UserResource handleRecordUpdate called', [
            'user_id' => $record->id,
            'data_keys' => array_keys($data),
        ]);

        if (isset($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
            unset($data['new_password']);
        }

        return $record;
    }
}
