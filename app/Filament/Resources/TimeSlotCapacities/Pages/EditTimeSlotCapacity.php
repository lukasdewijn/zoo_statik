<?php

namespace App\Filament\Resources\TimeSlotCapacities\Pages;

use App\Filament\Resources\TimeSlotCapacities\TimeSlotCapacityResource;
use App\Models\TimeSlotCapacity;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTimeSlotCapacity extends EditRecord
{
    protected static string $resource = TimeSlotCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Terug naar capaciteiten')
                ->url(TimeSlotCapacityResource::getUrl())
                ->color('gray'),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $exists = TimeSlotCapacity::query()
            ->whereDate('date', $data['date'])
            ->where('time_slot_id', $data['time_slot_id'])
            ->where('id', '!=', $this->record->id)
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
