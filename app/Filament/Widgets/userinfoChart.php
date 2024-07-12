<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\View\View;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class userinfoChart extends ApexChartWidget
{
    protected static ?string $heading = 'Chrrart';
    protected static string $color = 'warning';
    protected static ?string $maxHeight = '300px';
    protected static ?string $loadingIndicator = 'Loading...';


//    protected function getFilters(): ?array
//    {
//        return [
//            'today' => 'Today',
//            'week' => 'Last week',
//            'month' => 'Last month',
//            'year' => 'This year',
//        ];
//    }
    protected function getFormSchema(): array
    {
        return [

            TextInput::make('title')
                ->default('My Chart'),

            DatePicker::make('date_start')
                ->default('2023-01-01'),

            DatePicker::make('date_end')
                ->default('2023-12-31')

        ];
    }

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'column',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'BlogPostsChart',
                    'data' => [7, 4, 6, 10, 14, 7, 5, 9, 10, 15, 13, 18],
                ],
            ],
            'xaxis' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'],
        ];
    }
}
