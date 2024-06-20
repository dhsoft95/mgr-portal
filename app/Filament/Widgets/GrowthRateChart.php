<?php

namespace App\Filament\Widgets;

use App\Models\AppUser; // Ensure AppUser is imported
use Carbon\Carbon; // Ensure Carbon is imported
use Filament\Widgets\ChartWidget;

class GrowthRateChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth Rate Per Month';

    protected static string $color = 'success';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Retrieve and group user registrations by month
        $monthlyRegistrations = AppUser::query('id', 'created_at')->get()->groupBy(function ($user) {
            return Carbon::parse($user->created_at)->format('Y-m'); // Group by year-month
        });

        // Calculate monthly user counts
        $monthlyCounts = [];
        foreach ($monthlyRegistrations as $month => $users) {
            $monthlyCounts[$month] = $users->count();
        }

        // Calculate monthly growth rates
        $growthRates = [];
        $previousCount = null;
        foreach ($monthlyCounts as $month => $count) {
            if ($previousCount === null) {
                // No growth rate for the first month
                $growthRates[$month] = 0;
            } else {
                // Calculate growth rate as a percentage
                $growthRate = (($count - $previousCount) / $previousCount) * 100;
                $growthRates[$month] = round($growthRate, 2);
            }
            $previousCount = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'User Growth Rate (%)',
                    'data' => array_values($growthRates),
                ],
            ],
            'labels' => array_keys($growthRates),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
