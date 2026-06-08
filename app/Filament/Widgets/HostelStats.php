<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Guest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;


class HostelStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [

            Stat::make('Total Guests', Guest::count()),

            Stat::make('Total Bookings', Booking::count()),

            Stat::make(
                'Today Check-Ins',
                Booking::whereDate('check_in_date', '=', DB::raw('CURDATE()'))->count()
            ),

            Stat::make(
                'Today Check-Outs',
                Booking::whereDate('check_out_date', '=', DB::raw('CURDATE()'))->count()
            ),
        ];
    }
}
