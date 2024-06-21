<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class GeoMapChart extends Widget
{

    protected static string $view = 'filament.widgets.geo-map-chart';
    protected int | string | array $columnSpan = 1;

    public $chartData;

    public function mount(): void
    {
        $this->chartData = $this->getData();
    }

    protected function getData()
    {
        return [
            'labels' => ['Germany', 'United States', 'Brazil', 'Canada', 'France', 'Russia'],
            'datasets' => [
                [
                    'label' => 'Population',
                    'data' => [200, 300, 400, 500, 600, 700],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view(static::$view, ['chartData' => $this->chartData]);
    }
}
