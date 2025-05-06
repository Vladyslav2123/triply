<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-m-credit-card';

    public static function getPluralModelLabel(): string
    {
        return __('payment.plural');
    }

    public static function getModelLabel(): string
    {
        return __('payment.singular');
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('payment_method')
                    ->label(__('payment.method'))
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn ($record): string => $record->payment_method->value),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('reservation.reservationable.title')
                    ->label(__('payment.res_title'))
                    ->searchable()
                    ->sortable(false),
                Tables\Columns\TextColumn::make('reservation.guest.full_name')
                    ->label(__('payment.guest_full_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('payment.amount'))
                    ->numeric()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->money('USD', divideBy: 100)
                        ->label(__('payment.total'))),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('payment.paid_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('payment.method')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('payment.method'))
                    ->options(self::getEnumOptions(PaymentMethod::class))
                    ->placeholder(__('payment.all_methods')),
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

    private static function getEnumOptions($enum): array
    {
        return collect($enum::all())
            ->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', $value))])
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reservation_id')
                    ->label(__('payment.res_title'))
                    ->relationship('reservation.reservationable', 'title')
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->label(__('payment.method'))
                    ->options([
                        collect(PaymentMethod::all())
                            ->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', $value))])
                            ->toArray(),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount.formatted')
                    ->label(__('payment.formatted'))
                    ->required(),
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
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()->with(['reservation']);
    }
}
