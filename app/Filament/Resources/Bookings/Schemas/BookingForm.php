<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Property;
use App\Models\Room;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_reference')
                    ->required()
                    ->default(fn() => 'BK-' . strtoupper(uniqid()))
                    ->unique(table: 'bookings', column: 'booking_reference', ignoreRecord: true),

                // Step 1 — Property selector
                Select::make('property_id')
                    ->label('Property')
                    ->options(fn() => Property::query()->pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        // Reset room when property changes
                        $set('room_id', null);
                        $set('room_number', null);
                        $set('_capacity_info', null);
                    }),

                // Step 2 — Room selector (filtered by property, hides full rooms)
                Select::make('room_id')
                    ->label('Room')
                    ->options(function (Get $get) {
                        $propertyId = $get('property_id');

                        if (!$propertyId) {
                            return [];
                        }

                        return Room::query()
                            ->where('property_id', $propertyId)
                            ->where('is_available', true)  // hides fully booked rooms
                            ->get()
                            ->mapWithKeys(fn(Room $room) => [
                                $room->id => "{$room->title} (max {$room->capacity} guests)"
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->disabled(fn(Get $get) => blank($get('property_id')))
                    ->helperText(
                        fn(Get $get) => blank($get('property_id'))
                            ? 'Select a property first'
                            : null
                    )
                    ->afterStateUpdated(function (?int $state, Set $set) {
                        if (!$state) {
                            $set('room_number', null);
                            $set('_capacity_info', null);
                            return;
                        }

                        $room = Room::find($state);
                        if ($room) {
                            $set('room_number', $room->room_number);
                            $set('_capacity_info', "Max {$room->capacity} guests · {$room->current_guests} currently booked");
                        }
                    }),

                TextInput::make('room_number')
                    ->required(),

                // Capacity display — not saved to DB
                TextInput::make('_capacity_info')
                    ->label('Room capacity')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Select a room to see capacity'),

                DatePicker::make('check_in_date')
                    ->required(),

                DatePicker::make('check_out_date')
                    ->required(),

                TextInput::make('adults')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set) => self::validateCapacity($get, $set)),

                TextInput::make('children')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set) => self::validateCapacity($get, $set)),

                TextInput::make('room_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->rupees()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $roomPrice = floatval($state ?? 0);
                        $tax       = round($roomPrice * 0.18, 2);
                        $total     = round($roomPrice + $tax, 2);
                        $set('tax_amount', $tax);
                        $set('total_amount', $total);
                    }),

                TextInput::make('tax_amount')
                    ->label('Tax (18%)')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->rupees()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->rupees()
                    ->disabled()
                    ->dehydrated(),

                Select::make('booking_platform')
                    ->options([
                        'walk-in'  => "Walk-In's",
                        'website'  => 'Website',
                        'Third-Party Platform' => [
                            'mmt'         => 'MakeMyTrip',
                            'booking.com' => 'Booking.com',
                        ],
                    ])
                    ->searchable()
                    ->required(),

                Select::make('booking_status')
                    ->options([
                        'pending'   => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable()
                    ->required(),

                Select::make('payment_status')
                    ->options([
                        'paid'   => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->searchable()
                    ->required(),

                Select::make('payment_method')
                    ->options([
                        'cash'   => 'Cash',
                        'card'   => 'Credit/ Debit Card',
                        'online' => 'Online Payment',
                    ])
                    ->searchable()
                    ->required(),

                Textarea::make('special_requests')
                    ->columnSpanFull(),

                Textarea::make('internal_notes')
                    ->columnSpanFull(),
            ]);
    }

    private static function validateCapacity(Get $get, Set $set): void
    {
        $roomId   = $get('room_id');
        $adults   = intval($get('adults') ?? 1);
        $children = intval($get('children') ?? 0);

        if (!$roomId) return;

        $room     = Room::find($roomId);
        if (!$room) return;

        $available = $room->capacity - $room->current_guests;
        $total     = $adults + $children;

        if ($total > $available) {
            $maxChildren = max(0, $available - $adults);
            $set('children', $maxChildren);
        }

        $clamped = min($total, $available);
        $set('_capacity_info', "Max {$room->capacity} guests · {$room->current_guests} booked · {$available} available · {$clamped} selected");
    }
}
