<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use ArberMustafa\FilamentGoogleCharts\Widgets\PieChartWidget;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CountriesChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     *
     */
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $chartId = 'countriesChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Most Registered Countries';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get users grouped by country
        $usersByCountry = AppUser::select('country')
            ->get()
            ->groupBy('country')
            ->map(function ($users, $country) {
                return $users->count();
            });

        // Convert the collection to an array
        $countryData = $usersByCountry->toArray();

        // Chart data
        $data = [
            [
                'name' => 'Users by Country',
                'data' => array_values($countryData),
            ],
        ];

        // X-axis categories
        $categories = array_keys($countryData);

        // Chart options
        $options = [
            'chart' => [
                'type' => 'line',
                'height' => 300,
//                'width' => 900,
            ],
            'series' => $data,
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
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
            'stroke' => [
                'curve' => 'smooth',
            ],
        ];

        return $options;
    }
}


