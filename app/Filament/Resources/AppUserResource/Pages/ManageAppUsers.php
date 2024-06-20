<?php

namespace App\Filament\Resources\AppUserResource\Pages;

use App\Filament\Resources\AppUserResource;
use App\Filament\Widgets\GrowthRateChart;
use App\Filament\Widgets\TransOverview;
use App\Filament\Widgets\UsersOverview;
use Filament\Actions;
use   Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Filament\Resources\Pages\ManageRecords;

class ManageAppUsers extends ManageRecords
{
    protected static string $resource = AppUserResource::class;
    protected function getHeaderWidgets(): array
    {
        return[
            UsersOverview::class,
            GrowthRateChart::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

}
