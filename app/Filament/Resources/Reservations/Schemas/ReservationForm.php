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
                ->disabled(fn ($record) => $record?->status === 'cancelled')
                ->schema([
                    TextInput::make('public_code')
                        ->label('Public code')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Generated automatically.'),

                    DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->native(false),

                    Select::make('time_slot_id')
                    ->label('Timeslot')
                        ->relationship(
                            name: 'timeSlot',
                            titleAttribute: 'label',
                            modifyQueryUsing: fn ($query) => $query->where('active', 1),
                        )
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Active',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default(fn ($record) => $record?->status ?? 'confirmed'),

                ]),

            Section::make('Visitors')
                ->disabled(fn ($record) => $record?->status === 'cancelled')
                ->schema([
                    Repeater::make('visitors')
                        ->relationship()
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
