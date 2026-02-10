<?php

namespace App\Filament\Resources\Reservations\Schemas;

use App\Enums\ReservationStatus;
use App\Models\TimeSlotCapacity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reservation')
                ->columns(2)
                ->disabled(fn ($record) => $record?->status === ReservationStatus::Cancelled)
                ->schema([
                    TextInput::make('public_code')
                        ->label('Public code')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Generated automatically.'),

                    DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->native(false)
                        ->reactive(), // ✅

                    Select::make('time_slot_id')
                        ->label('Timeslot')
                        ->relationship(
                            name: 'timeSlot',
                            titleAttribute: 'start_time',
                            modifyQueryUsing: fn ($query) => $query->orderBy('start_time'),
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->label)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive() // ✅
                        ->helperText(function (Get $get): ?string {
                            $date = $get('date');
                            $slotId = $get('time_slot_id');

                            if (! $date || ! $slotId) {
                                return 'Select a date and timeslot to see remaining capacity.';
                            }

                            $capacity = TimeSlotCapacity::capacityFor($date, $slotId);
                            $reserved = TimeSlotCapacity::reservedCount($date, $slotId);
                            $remaining = max(0, $capacity - $reserved);

                            return "Remaining: {$remaining} places (capacity: {$capacity}, reserved visitors: {$reserved}).";
                        }),

                    Select::make('status')
                        ->label('Status')
                        ->options(ReservationStatus::class)
                        ->required()
                        ->default(fn ($record) => $record?->status ?? ReservationStatus::Confirmed),
                ]),

            Section::make('Visitors')
                ->disabled(fn ($record) => $record?->status === ReservationStatus::Cancelled)
                ->schema([
                    Repeater::make('visitors')
                        ->relationship()
                        ->minItems(1)
                        ->defaultItems(1)
                        ->columns(3)
                        ->maxItems(function (Get $get): int {
                            $date = $get('date');
                            $slotId = $get('time_slot_id');

                            if (! $date || ! $slotId) {
                                return 1;
                            }

                            return max(1, TimeSlotCapacity::remainingCapacity($date, $slotId));
                        })
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
                                ->maxLength(50)
                                ->rules([new \App\Rules\SubscriptionNumber]),
                        ]),
                ]),
        ]);
    }
}
