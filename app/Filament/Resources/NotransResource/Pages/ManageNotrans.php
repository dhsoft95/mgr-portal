<?php

namespace App\Filament\Resources\NotransResource\Pages;

use App\Filament\Resources\NotransResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotrans extends ManageRecords
{
    protected static string $resource = NotransResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
    public function getTableRecordKey(Model|\Illuminate\Database\Eloquent\Model $record): string
    {
        return uniqid();
    }
}
