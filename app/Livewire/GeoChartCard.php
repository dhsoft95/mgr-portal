<?php

namespace App\Livewire;

use Livewire\Component;

class GeoChartCard extends Component
{
    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.geo-chart-card');
    }
}
