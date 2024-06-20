<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransDataResource\Pages;
use App\Filament\Resources\TransDataResource\RelationManagers;
use App\Models\TransData;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class TransDataResource extends Resource
{
    protected static ?string $model = TransData::class;
    protected static ?string $navigationLabel = 'All-Transacting';
    protected static ?string $navigationGroup = 'Transaction Metrics';
    protected static ?string $modelLabel = 'All Transaction Records ';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('trxId')->label('Transaction Id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('status')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_amount')
                            ->numeric()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_channel_id')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_channel_currency')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_currency')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('created_at')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_channel_country')
                            ->required()
                            ->numeric()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_identity_value')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_identity_type')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_middle_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_last_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_type')
                            ->maxLength(40),
                        Forms\Components\TextInput::make('receiver_channel_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_sub_provider')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_id_value')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('receiver_channel_country')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_channel_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sender_currency_id')
                            ->maxLength(6),
                        Forms\Components\DateTimePicker::make('terminal_datetime'),
                        Forms\Components\TextInput::make('service_charge_collection_percent')
                            ->numeric(),
                        Forms\Components\TextInput::make('service_charge_collection')
                            ->numeric(),
                        Forms\Components\TextInput::make('service_charge_disbursement')
                            ->numeric(),
                        Forms\Components\TextInput::make('tax_tozo')
                            ->numeric(),
                        Forms\Components\TextInput::make('reason')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('exchange_rate')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('exchange_rate_receive')
                            ->numeric(),
                        Forms\Components\TextInput::make('exchange_rate_send')
                            ->numeric(),
                        Forms\Components\TextInput::make('sender_channel_first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sms_status')
                            ->default('unsent')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('tax')
                            ->required()
                            ->numeric()
                            ->default(0.00),

                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('trxId')->label('Transaction ID')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('status ')->searchable(isGlobal: true),
//
                Tables\Columns\TextColumn::make('sender_amount')->label('Sender Amount')->searchable(),
                Tables\Columns\TextColumn::make('sender_channel_id')->label('Sender ID')->searchable(),
                Tables\Columns\TextColumn::make('sender_channel_currency')->label('Sender Currency')->searchable(),
                Tables\Columns\TextColumn::make('receiver_amount')->label('Receiver Amount')->searchable(),
                Tables\Columns\TextColumn::make('receiver_channel_id')->label('Receiver ID')->searchable(),
                Tables\Columns\TextColumn::make('receiver_channel_currency')->label('Receiver Currency')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('created_at')->searchable(),
//                Tables\Columns\TextColumn::make('created_at')->label('Created at')->searchable()->sortable()->since()->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([

                SelectFilter::make('status')
                    ->options([
                        '4' => 'Failed',
                        '2' => 'Success',
                        '3' => 'Collected',
                        '1' => 'Pending',
                    ])->searchable()->multiple()
                    ->preload()->label('Filter by Status'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })->columnSpan(2)->columns(2)



            ],layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),

            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
                        TextEntry::make('created_at'),
                    ])->columns(3),

                Fieldset::make('Transaction Info')
                    ->schema([
                        TextEntry::make('trxId')->label('Transaction Id'),
                        TextEntry::make('status'),
//                        TextEntry::make('status')
//                            ->color(fn (string $state): string => match ($state) {
//                                'processing' => 'warning',
//                                'disbursed' => 'success',
//                                'Collected' => 'info',
//                                'failed' => 'danger',
//                            })->label('Status'),
                        TextEntry::make('sender_amount')->numeric(),
                        TextEntry::make('sender_channel_id'),
                        TextEntry::make('sender_channel_currency'),
                        TextEntry::make('receiver_channel_id'),
                        TextEntry::make('receiver_channel_currency'),
                        TextEntry::make('created_at'),
                        TextEntry::make('sender_channel_country')->numeric(),
                        TextEntry::make('sender_identity_value'),
                        TextEntry::make('sender_identity_type'),
                        TextEntry::make('receiver_channel_first_name'),
                        TextEntry::make('receiver_channel_middle_name'),
                        TextEntry::make('receiver_channel_last_name'),
                        TextEntry::make('receiver_channel_type'),
                        TextEntry::make('receiver_channel_name'),
                        TextEntry::make('receiver_channel_sub_provider'),
                        TextEntry::make('receiver_id_value'),
                        TextEntry::make('receiver_channel_country'),
                        TextEntry::make('sender_channel_name'),
                        TextEntry::make('sender_currency_id'),
                        TextEntry::make('terminal_datetime')->date(),
                        TextEntry::make('service_charge_collection_percent')->numeric(),
                        TextEntry::make('service_charge_collection')->numeric(),
                        TextEntry::make('service_charge_disbursement')->numeric(),
                        TextEntry::make('tax_tozo')->numeric(),
                        TextEntry::make('reason'),
                        TextEntry::make('exchange_rate')->numeric(),
                        TextEntry::make('exchange_rate_receive')->numeric(),
                        TextEntry::make('exchange_rate_send')->numeric(),
                        TextEntry::make('sender_channel_first_name'),
                        TextEntry::make('sms_status')->default('unsent'),
                        TextEntry::make('tax')->numeric(),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransData::route('/'),
        ];
    }
}
