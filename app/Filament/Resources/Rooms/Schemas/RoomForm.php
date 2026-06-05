<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('property_id')
                    ->required()
                    ->numeric(),
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
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('price')
                    ->rupees()
                    ->required()
                    ->numeric(),
                TextInput::make('total_inventory')
                    ->required()
                    ->numeric()
                    ->default(1),
                FileUpload::make('featured_room_image')
                    ->required()
                    ->image()
                    ->directory('room-images'),
            ]);
    }
}
