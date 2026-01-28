<?php

namespace App\Filament\Resources\TimeSlotCapacities\Tables;

use App\Models\Reservation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeSlotCapacitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->select('time_slot_capacities.*')
                    ->selectSub(
                        Reservation::query()
                            ->from('reservations')
                            ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
                            ->selectRaw('COUNT(visitors.id)')
                            ->whereColumn('reservations.timeslot_id', 'time_slot_capacities.time_slot_id')
                            ->whereColumn('reservations.date', 'time_slot_capacities.date')
                            ->where('reservations.status', 'confirmed'),
                        'reserved_count'
                    );
            })
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('timeSlot.start_time')
                    ->label('Start')
                    ->sortable(),

                TextColumn::make('timeSlot.end_time')
                    ->label('End')
                    ->sortable(),

                // handig: inline edit
                TextInputColumn::make('capacity')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->sortable(),

                TextColumn::make('reserved_count')
                    ->label('Reserved')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('left')
                    ->label('Left')
                    ->state(function ($record) {
                        $reserved = (int) ($record->reserved_count ?? 0);
                        return max(0, (int) $record->capacity - $reserved);
                    }),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['date'] ?? null,
                            fn (Builder $q, $date) => $q->whereDate('date', $date)
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
