<?php

namespace App\Filament\Resources;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.users');
    }

    public static function getModelLabel(): string
    {
        return __('users.profile');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.profiles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        self::getUserInfoSection(),
                        self::getUserPhotoSection(),
                        self::getUserProfileDetails(),
                        self::getUserLocationSection(),
                        self::getUserSocialSection(),
                        self::getUserPersonalSection(),
                        self::getUserTravelSection(),
                        self::getUserWorkSection(),
                        self::getUserBioSection(),
                        self::getNotificationPreferencesSection(),
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
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('users.first_name'))
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(['sm' => 1]),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('users.last_name'))
                            ->maxLength(100)
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\DatePicker::make('birth_date')
                            ->label(__('users.birth_date'))
                            ->columnSpan(['sm' => 1]),
                    ])
                    ->columns(2),
            ]);
    }

    private static function getUserPhotoSection(): Section
    {
        return Section::make(__('users.photo'))
            ->relationship('photo')
            ->schema([
                Forms\Components\FileUpload::make('url')
                    ->label(__('users.photo'))
                    ->image()
                    ->disk('s3')
                    ->directory('profiles')
                    ->visibility('publico')
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->imageEditorViewportWidth('100')
                    ->imageEditorViewportHeight('100')
                    ->imagePreviewHeight('50')
                    ->panelAspectRatio('1:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left'),
            ]);
    }

    private static function getUserProfileDetails(): Section
    {
        return Section::make(__('users.profile_details'))
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
            ->schema([
                Forms\Components\TextInput::make('biography_title')
                    ->label(__('users.biography_title'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('about')
                    ->label(__('users.about'))
                    ->rows(5),
            ]);
    }

    private static function getNotificationPreferencesSection(): Section
    {
        return Section::make(__('users.notification_preferences'))
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo.url')
                    ->label(__('users.photo'))
                    ->circular()
                    ->defaultImageUrl(fn (Profile $record): string => $record->photo?->url ?? Storage::url('users/default.png')),
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('users.name'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('users.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.role')
                    ->label(__('users.role'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_formatted')
                    ->label(__('users.location'))
                    ->icon('heroicon-m-map-pin'),
                Tables\Columns\TextColumn::make('is_superhost')
                    ->label(__('users.is_superhost'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('users.yes') : __('users.no'))
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable()
                    ->icon('heroicon-m-user')
                    ->grow(false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label(__('users.gender'))
                    ->options(Gender::options())
                    ->preload(),
                Tables\Filters\SelectFilter::make('education_level')
                    ->label(__('users.education_level'))
                    ->options(EducationLevel::options())
                    ->preload(),
                Tables\Filters\SelectFilter::make('languages')
                    ->label(__('users.languages'))
                    ->options(Language::options())
                    ->multiple()
                    ->query(function (EloquentBuilder $query, array $data) {
                        $values = $data['values'] ?? null;
                        if (blank($values)) {
                            return $query;
                        }

                        foreach ($values as $value) {
                            $query->whereJsonContains('languages', $value);
                        }

                        return $query;
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

                        foreach ($values as $value) {
                            $query->whereJsonContains('interests', $value);
                        }

                        return $query;
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('is_superhost')
                    ->label(__('users.is_superhost'))
                    ->query(fn (EloquentBuilder $query) => $query->where('is_superhost', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
            'view' => Pages\ViewProfile::route('/{record}'),
        ];
    }
}
