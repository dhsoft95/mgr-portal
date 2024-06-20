<?php

namespace App\Filament\Resources\TransDataResource\Pages;

use App\Filament\Resources\TransDataResource;
use App\Models\transData;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageTransData extends ManageRecords
{
    protected static string $resource = TransDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->badge(transData::query()->count()),
            'Today' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->startOfDay()))
                ->badge(transData::query()->where('created_at', '>=', now()->startOfDay())->count()),
            'This Week' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->subWeek()))
                ->badge(transData::query()->where('created_at', '>=', now()->subWeek())->count()),
            'This Month' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->subMonth()))
                ->badge(transData::query()->where('created_at', '>=', now()->subMonth())->count()),
            'This Year' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->subYear()))
                ->badge(transData::query()->where('created_at', '>=', now()->subYear())->count()),
        ];
    }
}
