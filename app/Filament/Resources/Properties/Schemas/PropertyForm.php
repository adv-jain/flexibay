<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),
                Toggle::make('is_available')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('featured_image')
                    ->image(),
                FileUpload::make('image_option_1')
                    ->image(),
                FileUpload::make('image_option_2')
                    ->image(),
                FileUpload::make('image_option_3')
                    ->image(),
                FileUpload::make('image_option_4')
                    ->image(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->rupees(),
                TextInput::make('combo_breakfast_lunch_dinner')->label('Combo: Breakfast + Lunch + Dinner')
                    ->numeric()
                    ->rupees(),
                TextInput::make('with_breakfast')
                    ->numeric()
                    ->rupees(),
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('postal_code'),
            ]);
    }
}
