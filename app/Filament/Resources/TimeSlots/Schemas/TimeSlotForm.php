<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('start_time')
                ->required()
                ->rule('regex:/^\d{2}:\d{2}$/'),
            TextInput::make('end_time')
                ->required()
                ->rule('regex:/^\d{2}:\d{2}$/'),
            Toggle::make('recurring')
                ->default(true),
        ]);
    }
}
