<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reservation')
                ->columns(2)
                ->schema([
                    TextInput::make('public_code')
                        ->label('Public code')
                        ->disabled()
                        ->dehydrated(false) // niet opslaan vanuit form
                        ->helperText('Generated automatically.'),

                    DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->native(false),

                    Select::make('time_slot_id') // PAS DIT AAN als jouw FK anders heet
                    ->label('Timeslot')
                        ->relationship(
                            name: 'timeSlot',
                            titleAttribute: 'label',
                            modifyQueryUsing: fn ($query) => $query->where('active', 1),
                        )
                        ->required()
                        ->searchable()
                        ->preload(),
                ]),

            Section::make('Visitors')
                ->schema([
                    Repeater::make('visitors')
                        ->relationship() // gebruikt Reservation->visitors()
                        ->minItems(1)
                        ->maxItems(3)
                        ->defaultItems(1)
                        ->columns(3)
                        ->schema([
                            TextInput::make('firstname')
                                ->label('First name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('lastname')
                                ->label('Last name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('subscription_nr')
                                ->label('Subscription #')
                                ->placeholder('Optional')
                                ->maxLength(50),
                        ]),
                ]),
        ]);
    }
}
