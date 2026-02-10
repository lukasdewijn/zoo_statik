<?php

namespace App\Filament\Resources\TimeSlotCapacities;

use App\Filament\Resources\TimeSlotCapacities\Pages\CreateTimeSlotCapacity;
use App\Filament\Resources\TimeSlotCapacities\Pages\EditTimeSlotCapacity;
use App\Filament\Resources\TimeSlotCapacities\Pages\ListTimeSlotCapacities;
use App\Filament\Resources\TimeSlotCapacities\Schemas\TimeSlotCapacityForm;
use App\Filament\Resources\TimeSlotCapacities\Tables\TimeSlotCapacitiesTable;
use App\Models\TimeSlotCapacity;
use BackedEnum;
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
        return TimeSlotCapacityForm::configure($schema);
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
