<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UserChart extends ChartWidget
{

    protected static ?string $heading = 'Registered   users Per Monthly';
    protected static string $color = 'success';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $trans=AppUser::query('id','created_at')->get()->groupBy(function ($trans){
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
                    'label' => 'Registered user Per Monthly',
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
