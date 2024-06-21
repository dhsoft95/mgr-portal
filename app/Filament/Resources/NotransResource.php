<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotransResource\Pages;
use App\Filament\Resources\NotransResource\RelationManagers;
use App\Models\Notrans;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class NotransResource extends Resource
{
    protected static ?string $model = Notrans::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Metrics';
    protected static ?string $navigationLabel = 'Non-Transacting Users';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->label('First Name')->searchable(),
                TextColumn::make('last_name')->label('Last Name')->searchable(),
                TextColumn::make('phone_number')->label('Phone Number')->searchable(),
                TextColumn::make('country')->label('Country')->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('first_name')
                    ->query(fn (Builder $query) => $query->first_name())
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNotrans::route('/'),
        ];
    }
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getURL('index');
    }
}
