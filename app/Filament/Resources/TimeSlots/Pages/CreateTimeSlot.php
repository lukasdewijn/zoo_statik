<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeSlot extends CreateRecord
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Terug naar tijdsloten')
                ->url(TimeSlotResource::getUrl())
                ->color('gray'),
        ];
    }
}
