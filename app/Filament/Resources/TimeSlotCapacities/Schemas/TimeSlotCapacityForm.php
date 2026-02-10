<?php

namespace App\Filament\Resources\TimeSlotCapacities\Schemas;

use App\Models\TimeSlotCapacity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TimeSlotCapacityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->reactive(),
                Select::make('time_slot_id')
                    ->label('Timeslot')
                    ->relationship(
                        name: 'timeSlot',
                        titleAttribute: 'start_time',
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->label)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->createOptionForm([
                        TextInput::make('start_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                        TextInput::make('end_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                        Toggle::make('recurring')->default(true),
                    ]),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(TimeSlotCapacity::DEFAULT_CAPACITY)
                    ->minValue(function (Get $get): int {
                        $date = $get('date');
                        $slotId = $get('time_slot_id');

                        if (! $date || ! $slotId) {
                            return 0;
                        }

                        return TimeSlotCapacity::reservedCount($date, $slotId);
                    })
                    ->helperText(function (Get $get): ?string {
                        $date = $get('date');
                        $slotId = $get('time_slot_id');

                        if (! $date || ! $slotId) {
                            return null;
                        }

                        $reserved = TimeSlotCapacity::reservedCount($date, $slotId);

                        return "Currently {$reserved} visitors reserved.";
                    }),
            ]);
    }
}
