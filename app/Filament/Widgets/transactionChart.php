<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use App\Models\transData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class transactionChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions Per Monthly';

    protected function getData(): array
    {
        $trans=transData::query('id','created_at')->get()->groupBy(function ($trans){
            return Carbon::parse( $trans->created_at)->format('F');
        });
//       dd($trans);
        $quantities=[];
        foreach ($trans as $tran=>$value){
            array_push($quantities,$value->count());
        }
        return [
            'datasets' => [
                [
                    'label' => 'Transactions  Per Monthly',
                    'data' => $quantities,
                ],
            ],
            'labels' =>  $trans->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
