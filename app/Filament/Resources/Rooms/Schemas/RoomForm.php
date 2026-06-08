<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class RoomForm
{
    /**
     * Get the room-specific input fields as an unpackable array.
     */
    public static function configureFields(): array
    {
        return [
            Select::make('room_type')
                ->required()
                ->options([
                    'standard_room' => 'Standard Room',
                    'deluxe_room' => 'Deluxe Room',
                    'double_room' => 'Double Room',
                    'double_deluxe_room' => 'Double Deluxe Room',
                    'triple_room' => 'Triple Room',
                    'triple_deluxe_room' => 'Triple Deluxe Room',
                ]),

            TextInput::make('title')
                ->required(),

            TextInput::make('capacity')
                ->label('Capacity (Occupancy)') // FIXED: More descriptive label for capacity field
                ->required()
                ->numeric()
                ->default(1),

            TextInput::make('price')
                ->label('Price Per Night') // FIXED: More descriptive label for price field
                ->prefix('₹') // FIXED: Standard native prefix replacement for custom currency method
                ->required()
                ->numeric(),

            TextInput::make('total_inventory')
                ->label('Total Inventory') // FIXED: More descriptive label for inventory field
                ->required()
                ->numeric()
                ->default(1),

            FileUpload::make('featured_room_image')
                ->required()
                ->image()
                ->directory('room-images'),
        ];
    }
}
