<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reservation')
                ->columns(2)
                ->schema([
                    TextEntry::make('public_code')
                        ->label('Code')
                        ->copyable()
                        ->columnSpanFull(),

                    TextEntry::make('date')
                        ->label('Date')
                        ->date('D d M Y'),

                    TextEntry::make('timeSlot.label')
                        ->label('Timeslot'),

                    TextEntry::make('visitors_count')
                        ->label('# Visitors')
                        ->numeric(),
                ]),
            Section::make('Visitors')
                ->schema([
                    RepeatableEntry::make('visitors')
                        ->label(false)
                        ->columns(3)
                        ->schema([
                            TextEntry::make('firstname')
                                ->label('First Name'),
                            TextEntry::make('lastname')
                                ->label('Last Name'),
                            TextEntry::make('subscription_nr')
                                ->label('Subscription #')
                                ->placeholder('–')
                                ->badge(),
                        ]),
                ]),

            Section::make('Metadata')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Created at')
                        ->dateTime(),

                    TextEntry::make('updated_at')
                        ->label('Updated at')
                        ->dateTime(),
                ]),

        ]);

    }
}
