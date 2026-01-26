<?php

namespace App\Filament\Resources\TimeSlotCapacities\Pages;

use App\Filament\Resources\TimeSlotCapacities\TimeSlotCapacityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimeSlotCapacity extends EditRecord
{
    protected static string $resource = TimeSlotCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
