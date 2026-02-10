<?php

namespace App\Filament\Resources\TimeSlotCapacities\Pages;

use App\Filament\Resources\TimeSlotCapacities\TimeSlotCapacityResource;
use App\Models\TimeSlotCapacity;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeSlotCapacity extends CreateRecord
{
    protected static string $resource = TimeSlotCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Terug naar capaciteiten')
                ->url(TimeSlotCapacityResource::getUrl())
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $exists = TimeSlotCapacity::query()
            ->whereDate('date', $data['date'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Er bestaat al een capacity voor deze datum en dit tijdslot.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
