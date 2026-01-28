<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->label('Timeslot')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('visitors_count')
                    ->label('# Visitors')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'cancelled' => 'danger',
                        'active' => 'success',
                        default => 'gray',
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
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->disabled(fn ($record) => $record->status === 'cancelled'),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'cancelled')
                    ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
