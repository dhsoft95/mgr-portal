<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MostTransUserOverview extends BaseWidget
{

    protected static ?string $heading = 'Most Transacted Users';
    protected static string $color = 'info';
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return AppUser::select(
                    'users.phone_number',
                    'users.first_name',
                    'users.last_name',
                    \DB::raw('COUNT(*) AS total_transactions') // Calculate total transactions
                )
                    ->join('tbl_transactions', function ($join) {
                        $join->on('users.phone_number', '=', 'tbl_transactions.sender_phone')
                            ->orOn('users.phone_number', '=', 'tbl_transactions.receiver_phone');
                    })->take(5)
                    ->where('tbl_transactions.status', 3) // Adjust the value of tbl_transactions.status as needed
                    ->groupBy('users.phone_number', 'users.first_name', 'users.last_name')
                    ->orderByRaw('COUNT(tbl_transactions.id) DESC')
                    ->take(4);
            })
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone Number'),
                \Filament\Tables\Columns\TextColumn::make('first_name')
                    ->label('First Name'),
                \Filament\Tables\Columns\TextColumn::make('last_name')
                    ->label('Last Name'),
                \Filament\Tables\Columns\TextColumn::make('total_transactions')
                    ->label('Total') ->color('success')// Display total transactions
            ]);
    }

    public function getTableRecordKey(Model|\Illuminate\Database\Eloquent\Model $record): string
    {
        return uniqid();
    }
}
