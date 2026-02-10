<?php

namespace App\Filament\Resources\TimeSlotCapacities\Schemas;

use App\Models\TimeSlotCapacity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeSlotCapacityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
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
                    ->createOptionForm([
                        TextInput::make('start_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                        TextInput::make('end_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                        Toggle::make('recurring')->default(true),
                    ]),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(TimeSlotCapacity::DEFAULT_CAPACITY),
            ]);
    }
}
