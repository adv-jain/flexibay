<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Chart';

    // Optional: Add a description
    protected ?string $description = 'Monthly revenue from bookings';

    protected function getData(): array
    {
        // Get last 12 months of revenue data
        $revenueData = $this->getMonthlyRevenue();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenueData['revenues'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // Blue with transparency
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $revenueData['months'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Can also be 'line' or 'pie'
    }

    // Optional: Customize chart height
    protected function getHeight(): int
    {
        return 300;
    }

    private function getMonthlyRevenue(): array
    {
        // Get last 12 months
        $months = collect();
        $revenues = collect();

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months->push($month->format('M Y'));

            // Calculate revenue for this month
            $revenue = Booking::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount'); // Assuming your booking has 'total_amount' field

            $revenues->push($revenue);
        }

        return [
            'months' => $months->toArray(),
            'revenues' => $revenues->toArray(),
        ];
    }
}
