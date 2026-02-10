<?php

namespace App\Filament\Resources\TimeSlotCapacities;

use App\Filament\Resources\TimeSlotCapacities\Pages\CreateTimeSlotCapacity;
use App\Filament\Resources\TimeSlotCapacities\Pages\EditTimeSlotCapacity;
use App\Filament\Resources\TimeSlotCapacities\Pages\ListTimeSlotCapacities;
use App\Filament\Resources\TimeSlotCapacities\Tables\TimeSlotCapacitiesTable;
use App\Models\TimeSlotCapacity;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimeSlotCapacityResource extends Resource
{
    protected static ?string $model = TimeSlotCapacity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'TimeSlotCapacity';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            DatePicker::make('date')
                ->required(),

            Select::make('time_slot_id')
                ->label('Time Slot')
                ->relationship('timeSlot', 'start_time', fn ($query) => $query->orderBy('start_time'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->label)
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('start_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                    TextInput::make('end_time')->required()->rule('regex:/^\d{2}:\d{2}$/'),
                    Toggle::make('recurring')->default(true),
                ]),

            TextInput::make('capacity')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default(200),
        ]);
    }

    public static function table(Table $table): Table
    {
        return TimeSlotCapacitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeSlotCapacities::route('/'),
            'create' => CreateTimeSlotCapacity::route('/create'),
            'edit' => EditTimeSlotCapacity::route('/{record}/edit'),
        ];
    }
}
