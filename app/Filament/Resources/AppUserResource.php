<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppUserResource\Pages;
use App\Filament\Resources\AppUserResource\RelationManagers;
use App\Models\AppUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppUserResource extends Resource


{
    protected static ?string $model = AppUser::class;

    protected static ?string $navigationGroup = 'User Metrics';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'All Registered Users ';

    protected static ?string $navigationLabel = 'All-Registered Users';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('role_id')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
//                Forms\Components\TextInput::make('password')
//                    ->password()
//                    ->required()
//                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->maxLength(13),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('identity_type')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('identity_value')
                    ->maxLength(255)
                    ->default(1),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\TextInput::make('gender')
                    ->maxLength(6),
                Forms\Components\TextInput::make('address')
                    ->maxLength(100),
                Forms\Components\TextInput::make('city')
                    ->maxLength(100),
                Forms\Components\TextInput::make('state')
                    ->maxLength(100),
                Forms\Components\TextInput::make('country')
                    ->maxLength(100),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(10)
                    ->default('unverified'),
                Forms\Components\TextInput::make('otp_pin')
                    ->maxLength(10),
                Forms\Components\DateTimePicker::make('phone_verified_at')
                    ->required(),
                Forms\Components\TextInput::make('subscription_status')
                    ->numeric()
                    ->default(1),
            ]);
    }
    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist

            ->schema([

                Fieldset::make('Users Info')
                            ->schema([
                                TextEntry::make('first_name'),
                                TextEntry::make('last_name'),
                                TextEntry::make('phone_number'),
                                TextEntry::make('birth_date')->date(),
                                TextEntry::make('gender'),
                                TextEntry::make('otp_pin'),
                                TextEntry::make('country'),
                                TextEntry::make('created_at')
                            ]) ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('otp_pin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')->words(3)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()->sortable()->sortable('ASC')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppUsers::route('/'),
        ];
    }
}
