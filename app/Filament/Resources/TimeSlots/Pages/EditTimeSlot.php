<?php

namespace App\Filament\Resources\TimeSlots\Pages;

use App\Filament\Resources\TimeSlots\TimeSlotResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTimeSlot extends EditRecord
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Terug naar tijdsloten')
                ->url(TimeSlotResource::getUrl())
                ->color('gray'),
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    if ($this->record->hasFutureReservations()) {
                        Notification::make()
                            ->title('Dit tijdslot heeft nog toekomstige reserveringen en kan niet verwijderd worden.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

}
