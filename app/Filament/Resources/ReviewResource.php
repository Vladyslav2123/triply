<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use App\Models\User;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.reviews');
    }

    public static function getPluralModelLabel(): string
    {
        return __('review.plural');
    }

    public static function getModelLabel(): string
    {
        return __('review.singular');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Review')
                    ->tabs([
                        Tabs\Tab::make(__('review.basic_info'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('review.reservation_details'))
                                    ->description(__('review.reservation_details_description'))
                                    ->schema([
                                        Forms\Components\Select::make('reservation_id')
                                            ->label(__('review.res_title'))
                                            ->relationship('reservation', 'id')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->reservationable?->title ?? $record->id)
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('reviewer_id')
                                            ->label(__('review.reviewer'))
                                            ->relationship('reviewer', 'email')
                                            ->getOptionLabelFromRecordUsing(fn (User $record) => $record->profile?->full_name ?? $record->email)
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make(__('review.ratings'))
                            ->icon('heroicon-o-star')
                            ->schema([
                                Section::make(__('review.overall_rating_section'))
                                    ->description(__('review.overall_rating_description'))
                                    ->schema([
                                        Forms\Components\TextInput::make('overall_rating')
                                            ->label(__('review.overall_rating'))
                                            ->helperText(__('review.rating_helper_text'))
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->step(0.1)
                                            ->minValue(1)
                                            ->maxValue(5)
                                            ->required(),
                                    ]),
                                Section::make(__('review.detailed_ratings'))
                                    ->description(__('review.detailed_ratings_description'))
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('cleanliness_rating')
                                                    ->label(__('review.cleanliness_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                                Forms\Components\TextInput::make('accuracy_rating')
                                                    ->label(__('review.accuracy_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('checkin_rating')
                                                    ->label(__('review.checkin_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                                Forms\Components\TextInput::make('communication_rating')
                                                    ->label(__('review.communication_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('location_rating')
                                                    ->label(__('review.location_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                                Forms\Components\TextInput::make('value_rating')
                                                    ->label(__('review.value_rating'))
                                                    ->numeric()
                                                    ->inputMode('integer')
                                                    ->minValue(1)
                                                    ->maxValue(5)
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                    ]),
                            ]),
                        Tabs\Tab::make(__('review.comment_tab'))
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make(__('review.comment_section'))
                                    ->description(__('review.comment_description'))
                                    ->schema([
                                        Forms\Components\Textarea::make('comment')
                                            ->label(__('review.comment'))
                                            ->placeholder(__('review.comment_placeholder'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('reservation_title')
                            ->label(__('review.res_title'))
                            ->getStateUsing(fn ($record) => $record->reservation?->reservationable?->title ?? '-')
                            ->searchable(query: function (EloquentBuilder $query, string $search): EloquentBuilder {
                                return $query->whereHas('reservation.reservationable', function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%");
                                });
                            })
                            ->weight(FontWeight::Bold)
                            ->sortable(),
                        Tables\Columns\TextColumn::make('reviewer.profile.full_name')
                            ->label(__('review.reviewer'))
                            ->searchable(query: function (EloquentBuilder $query, string $search): EloquentBuilder {
                                return $query->whereHas('reviewer.profile', function ($query) use ($search) {
                                    $query->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%");
                                });
                            })
                            ->sortable(),
                    ]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('created_at')
                            ->label(__('review.created_at'))
                            ->date('M d, Y')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('comment')
                            ->label(__('review.comment'))
                            ->limit(50)
                            ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                                $state = $column->getState();

                                if (strlen($state) <= $column->getCharacterLimit()) {
                                    return null;
                                }

                                return $state;
                            }),
                    ]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('overall_rating')
                            ->label(__('review.overall_rating'))
                            ->numeric()
                            ->suffix('/5')
                            ->sortable()
                            ->summarize(Average::make()->label(__('review.average_rating'))),
                        Tables\Columns\TextColumn::make('rating_stars')
                            ->label(__('review.rating'))
                            ->getStateUsing(function ($record) {
                                $rating = round($record->overall_rating);
                                $stars = str_repeat('⭐', $rating);

                                return $stars;
                            }),
                    ]),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('overall_rating')
                    ->label(__('review.overall_rating'))
                    ->options([
                        '1' => '⭐ (1)',
                        '2' => '⭐⭐ (1-2)',
                        '3' => '⭐⭐⭐ (2-3)',
                        '4' => '⭐⭐⭐⭐ (3-4)',
                        '5' => '⭐⭐⭐⭐⭐ (4-5)',
                    ])
                    ->query(function (EloquentBuilder $query, array $data) {
                        $value = $data['value'];

                        $ranges = [
                            '1' => [0, 1],
                            '2' => [1.1, 2],
                            '3' => [2.1, 3],
                            '4' => [3.1, 4],
                            '5' => [4.1, 5],
                        ];

                        if (isset($ranges[$value])) {
                            return $query->whereBetween('overall_rating', $ranges[$value]);
                        }

                        return $query;
                    }),
                Tables\Filters\Filter::make('has_comment')
                    ->label(__('review.has_comment'))
                    ->query(fn (EloquentBuilder $query) => $query->whereNotNull('comment'))
                    ->toggle(),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('review.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('review.created_until')),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()->with(['reservation', 'reviewer', 'reviewer.profile']);
    }
}
