<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PropertyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IconEntry::make('is_available')
                    ->boolean(),
                TextEntry::make('id')
                    ->label('Property ID'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ImageEntry::make('featured_image')
                    ->placeholder('-'),
                ImageEntry::make('image_option_1')
                    ->placeholder('-'),
                ImageEntry::make('image_option_2')
                    ->placeholder('-'),
                ImageEntry::make('image_option_3')
                    ->placeholder('-'),
                ImageEntry::make('image_option_4')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money('INR'),
                TextEntry::make('combo_breakfast_lunch_dinner')
                    ->label('Combo: Breakfast + Lunch + Dinner')
                    ->money('INR')
                    ->placeholder('-'),
                TextEntry::make('with_breakfast')
                    ->label('With Breakfast')
                    ->money('INR')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('state')
                    ->placeholder('-'),
                TextEntry::make('postal_code')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
