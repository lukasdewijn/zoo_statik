<?php

namespace App\Filament\Resources\TimeSlots\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_time', 'asc')
            ->columns([
                TextColumn::make('start_time')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->sortable(),
                IconColumn::make('recurring')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->hasFutureReservations()) {
                            Notification::make()
                                ->title('Dit tijdslot heeft nog toekomstige reserveringen en kan niet verwijderd worden.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
