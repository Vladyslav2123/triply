<?php

namespace App\Filament\Tables\Filters;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class EnumSelectFilter extends SelectFilter
{
    /**
     * Перевизначаємо метод getFormField для коректної роботи з Enum-ами
     */
    public function getFormField(): Select
    {
        $field = parent::getFormField();

        if ($this->queriesRelationships()) {
            // Додаємо обробник для перетворення Enum на рядок
            $field->getOptionLabelFromRecordUsing(function ($record) {
                $attribute = $this->getRelationshipTitleAttribute();
                $value = $record->{$attribute};

                // Якщо значення є Enum, отримуємо його рядкове представлення
                if ($value instanceof BackedEnum) {
                    return $value->value;
                }

                return $value;
            });
        }

        return $field;
    }

    /**
     * Перевизначаємо метод apply для коректної роботи з Enum-ами
     */
    public function apply(Builder $query, array $data = []): Builder
    {
        if ($this->evaluate($this->isStatic)) {
            return $query;
        }

        if ($this->hasQueryModificationCallback()) {
            return parent::apply($query, $data);
        }

        $isMultiple = $this->isMultiple();

        $values = $isMultiple ?
            $data['values'] ?? null :
            $data['value'] ?? null;

        if (blank($values)) {
            return $query;
        }

        if (! $this->queriesRelationships()) {
            return $query->{$isMultiple ? 'whereIn' : 'where'}(
                $this->getAttribute(),
                $values,
            );
        }

        return $query->whereHas(
            $this->getRelationshipName(),
            function (Builder $query) use ($isMultiple, $values) {
                if ($this->modifyRelationshipQueryUsing) {
                    $query = $this->evaluate($this->modifyRelationshipQueryUsing, [
                        'query' => $query,
                    ]) ?? $query;
                }

                if ($relationshipKey = $this->getRelationshipKey($query)) {
                    return $query->{$isMultiple ? 'whereIn' : 'where'}(
                        $relationshipKey,
                        $values,
                    );
                }

                return $query->whereKey($values);
            },
        );
    }

    /**
     * Додаємо метод для зручного створення фільтра з Enum-ами
     */
    public static function makeFromEnum(string $name, string $enumClass): static
    {
        return static::make($name)
            ->options(
                collect($enumClass::cases())
                    ->mapWithKeys(fn (BackedEnum $enum) => [$enum->value => $enum->name])
                    ->toArray()
            );
    }
}
