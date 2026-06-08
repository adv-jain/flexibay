<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Property;
use App\Models\Room;
use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;



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
                        $set('room_id', null);
                        $set('check_in_date', null);
                        $set('check_out_date', null);
                    }),

                // Step 2 — Room selector (filtered by property and date availability)
                Select::make('room_id')
                    ->label('Room')
                    ->options(function (Get $get) {
                        $propertyId = $get('property_id');
                        $checkIn = $get('check_in_date');
                        $checkOut = $get('check_out_date');

                        if (!$propertyId || !$checkIn || !$checkOut) {
                            return [];
                        }

                        return Room::query()
                            ->where('property_id', $propertyId)
                            ->where('is_available', true)
                            ->get()
                            ->filter(function (Room $room) use ($checkIn, $checkOut) {
                                return self::isRoomAvailableForDates($room, $checkIn, $checkOut);
                            })
                            ->mapWithKeys(fn(Room $room) => [
                                $room->id => "{$room->title} (Inventory: {$room->total_inventory})"
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->disabled(fn(Get $get) => blank($get('property_id')) || blank($get('check_in_date')) || blank($get('check_out_date')))
                    ->helperText(function (Get $get) {
                        if (blank($get('property_id'))) return 'Select a property first';
                        if (blank($get('check_in_date')) || blank($get('check_out_date'))) return 'Select check-in and check-out dates first';
                        return null;
                    })
                    ->afterStateUpdated(function (?int $state, Set $set, Get $get) {
                        if (!$state) {
                            $set('room_price', null);
                            $set('tax_amount', null);
                            $set('total_amount', null);
                            return;
                        }

                        $room = Room::find($state);
                        if ($room) {
                            $roomPrice = floatval($room->price ?? 0);
                            $set('room_price', $roomPrice);

                            // MANUALLY TRIGGER TAX AND TOTAL CALCULATION
                            self::calculateTaxAndTotal($roomPrice, $set);
                        }

                        // Show availability summary
                        $checkIn = $get('check_in_date');
                        $checkOut = $get('check_out_date');
                        if ($checkIn && $checkOut) {
                            $availability = self::getRoomAvailabilitySummary($room, $checkIn, $checkOut);
                            \Filament\Notifications\Notification::make()
                                ->title('Room Availability')
                                ->body($availability)
                                ->info()
                                ->send();
                        }
                    }),

                DatePicker::make('check_in_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('room_id', null);
                        $set('room_price', null);

                        // Validate date range
                        $checkOut = $get('check_out_date');
                        if ($checkOut && $get('check_in_date') >= $checkOut) {
                            $set('check_out_date', null);
                        }
                    })
                    ->minDate(now()->startOfDay()),

                DatePicker::make('check_out_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $set('room_id', null);
                        $set('room_price', null);
                    })
                    ->after(fn(Get $get) => $get('check_in_date')),


                // Add room_number field (make it hidden or visible)
                TextInput::make('room_number')
                    ->required()
                    ->default(null),
                TextInput::make('room_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->rupees()
                    ->live(true)
                    ->reactive() // This makes it react to changes
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $roomPrice = floatval($state ?? 0);
                        // $tax = round($roomPrice * 0.18, 2);
                        // $total = round($roomPrice + $tax, 2);
                        // $set('tax_amount', $tax);
                        // $set('total_amount', $total);
                        self::calculateTaxAndTotal($roomPrice, $set);
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

    /**
     * Check if room is available for all dates in the range
     */
    private static function isRoomAvailableForDates(Room $room, string $checkIn, string $checkOut): bool
    {
        $startDate = Carbon::parse($checkIn);
        $endDate = Carbon::parse($checkOut);

        // For same-day bookings, check just that one day
        if ($startDate->isSameDay($endDate)) {
            $bookedCount = self::getBookedCountForDate($room->id, $startDate);
            return $bookedCount < $room->total_inventory;
        }

        // For multi-day bookings, check each day excluding checkout day
        $endDate = $endDate->subDay();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $bookedCount = self::getBookedCountForDate($room->id, $date);

            if ($bookedCount >= $room->total_inventory) {
                return false;
            }
        }

        return true;
    }


    /**
     * Get number of bookings for a specific date
     */
    private static function getBookedCountForDate(int $roomId, Carbon $date): int
    {
        return Booking::where('room_id', $roomId)
            ->where(function ($query) use ($date) {
                $query->where(function ($subQuery) use ($date) {
                    // Booking spans over this date (check-in <= date < check-out)
                    $subQuery->whereDate('check_in_date', '<=', $date)
                        ->whereDate('check_out_date', '>', $date);
                })->orWhere(function ($subQuery) use ($date) {
                    // For same-day bookings (check-in = check-out = date)
                    $subQuery->whereDate('check_in_date', '=', $date)
                        ->whereDate('check_out_date', '=', $date);
                });
            })
            ->whereIn('booking_status', ['confirmed', 'pending'])
            ->count();
    }

    /**
     * Get availability summary for a room over date range
     */
    private static function getRoomAvailabilitySummary(Room $room, string $checkIn, string $checkOut): string
    {
        $startDate = Carbon::parse($checkIn);
        $endDate = Carbon::parse($checkOut)->subDay();

        $unavailableDates = [];
        $availableCount = 0;
        $totalDays = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $totalDays++;
            $bookedCount = self::getBookedCountForDate($room->id, $date);
            $available = $room->total_inventory - $bookedCount;

            if ($available > 0) {
                $availableCount++;
            } else {
                $unavailableDates[] = $date->format('M d');
            }
        }

        if (count($unavailableDates) > 0) {
            $dates = implode(', ', array_slice($unavailableDates, 0, 3));
            if (count($unavailableDates) > 3) {
                $dates .= " and " . (count($unavailableDates) - 3) . " more";
            }
            return "⚠️ Room is fully booked on: {$dates}. Please select different dates.";
        }

        return "✓ Room available for all selected dates. Total inventory: {$room->total_inventory}";
    }

    private static function calculateTaxAndTotal(float $roomPrice, Set $set): void
    {
        $tax = round($roomPrice * 0.18, 2);
        $total = round($roomPrice + $tax, 2);
        $set('tax_amount', $tax);
        $set('total_amount', $total);
    }
}
