<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperienceResource\Pages;
use App\Models\Experience;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExperienceResource extends Resource
{
    protected static ?string $model = Experience::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('host_id')
                    ->relationship('host', 'name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('seo'),
                Forms\Components\TextInput::make('location')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(32),
                Forms\Components\TextInput::make('languages')
                    ->required(),
                Forms\Components\TextInput::make('sub_category')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('reviews')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('duration')
                    ->required(),
                Forms\Components\TextInput::make('location_note')
                    ->maxLength(255),
                Forms\Components\TextInput::make('location_subtype')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('host_bio')
                    ->required(),
                Forms\Components\TextInput::make('address')
                    ->required(),
                Forms\Components\TextInput::make('host_provides'),
                Forms\Components\TextInput::make('guest_needs'),
                Forms\Components\TextInput::make('guest_requirements')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('grouping')
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\TextInput::make('pricing')
                    ->required(),
                Forms\Components\TextInput::make('discounts')
                    ->required(),
                Forms\Components\TextInput::make('booking_rules')
                    ->required(),
                Forms\Components\TextInput::make('cancellation_policy')
                    ->required(),
                Forms\Components\TextInput::make('category')
                    ->required(),
                Forms\Components\TextInput::make('location_type')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviews')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_note')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location_subtype')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('location_type'),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListExperiences::route('/'),
            'create' => Pages\CreateExperience::route('/create'),
            'edit' => Pages\EditExperience::route('/{record}/edit'),
        ];
    }
}
