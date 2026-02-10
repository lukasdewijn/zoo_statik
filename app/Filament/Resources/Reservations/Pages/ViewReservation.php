<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Terug naar reservaties')
                ->url(ReservationResource::getUrl())
                ->color('gray'),
            EditAction::make()
                ->visible(fn () => $this->record->status === ReservationStatus::Confirmed),
        ];
    }
}
