<?php

namespace App\Filament\Resources\Reservations\Tables;

use App\Enums\ReservationStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Filament\Filters\DateRangeFilter;
use App\Models\TimeSlot;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'asc')
            ->columns([
                TextColumn::make('public_code')
                    ->label('Code')
                    ->limit(8)
                    ->copyable()
                    ->searchable(),

                TextColumn::make('date')
                    ->date('D d M Y')
                    ->sortable(),

                TextColumn::make('timeSlot.label')
                    ->label('Timeslot'),

                TextColumn::make('visitors_count')
                    ->label('# Visitors')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ReservationStatus $state) => match ($state) {
                        ReservationStatus::Cancelled => 'danger',
                        ReservationStatus::Confirmed => 'success',
                        ReservationStatus::Completed => 'info',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('date'),
                SelectFilter::make('time_slot_id')
                    ->label('Tijdslot')
                    ->options(fn () => TimeSlot::all()->pluck('label', 'id')),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->disabled(fn ($record) => $record->status === ReservationStatus::Cancelled),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== ReservationStatus::Cancelled)
                    ->action(fn ($record) => $record->update(['status' => ReservationStatus::Cancelled, 'cancelled_at' => now()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
