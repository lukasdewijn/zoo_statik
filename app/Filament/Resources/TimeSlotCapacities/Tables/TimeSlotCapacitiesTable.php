<?php

namespace App\Filament\Resources\TimeSlotCapacities\Tables;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Filters\DateRangeFilter;
use App\Models\TimeSlot;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeSlotCapacitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                $query->select('time_slot_capacities.*')
                    ->selectSub(
                        Reservation::query()
                            ->from('reservations')
                            ->join('visitors', 'visitors.reservation_id', '=', 'reservations.id')
                            ->selectRaw('COUNT(visitors.id)')
                            ->whereColumn('reservations.time_slot_id', 'time_slot_capacities.time_slot_id')
                            ->whereColumn('reservations.date', 'time_slot_capacities.date')
                            ->where('reservations.status', ReservationStatus::Confirmed),
                        'reserved_count'
                    );
            })
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('timeSlot.label')
                    ->label('Tijdslot'),

                // Inline edit
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
                DateRangeFilter::make('time_slot_capacities.date'),
                SelectFilter::make('time_slot_id')
                    ->label('Tijdslot')
                    ->options(fn () => TimeSlot::all()->pluck('label', 'id')),
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
