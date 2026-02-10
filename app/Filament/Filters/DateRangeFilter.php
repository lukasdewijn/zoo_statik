<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter
{
    public static function make(string $column = 'date'): Filter
    {
        return Filter::make('date_range')
            ->form([
                DatePicker::make('from')
                    ->label('Van')
                    ->default(now()->toDateString()),
                DatePicker::make('until')
                    ->label('Tot'),
            ])
            ->query(function (Builder $query, array $data) use ($column) {
                return $query
                    ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->where($column, '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->where($column, '<=', $date));
            });
    }
}
