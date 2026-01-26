<?php

namespace App\Filament\Resources\TimeSlotCapacities\Pages;

use App\Filament\Resources\TimeSlotCapacities\TimeSlotCapacityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeSlotCapacities extends ListRecords
{
    protected static string $resource = TimeSlotCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
