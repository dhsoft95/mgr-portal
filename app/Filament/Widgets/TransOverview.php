<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use App\Models\transData;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Processing transactions with status '1'
        $processingCount = transData::where('status', '=', '1')->count();

        // Sum of all sender_amount across all transactions
        $allTransactionSum = number_format(transData::sum('sender_amount'), 2);

        // Failed Transactions
        $failedCount = transData::where('status', 4)->count();
        $failedSum = number_format(transData::where('status', 4)->sum('sender_amount'), 2);

        // Collected Transactions
        $collectedCount = transData::where('status', 3)->count();
        $collectedSum = number_format(transData::where('status', 3)->sum('sender_amount'), 2);

        // Disbursed Transactions
        $disbursedCount = transData::where('status', 3)->count('receiver_amount');
        $disbursedSum = number_format(transData::where('status', 3)->sum('receiver_amount'), 2);

        return [
            Stat::make('Processing Transactions', $processingCount)
                ->descriptionIcon('heroicon-m-receipt-refund')
                ->chart($this->generateChart())
                ->color($processingCount > 0 ? 'info' : 'gray'),

            Stat::make('Total Collected Amount', $collectedSum)
                ->descriptionIcon('heroicon-m-receipt-refund')
                ->chart($this->generateChart())
                ->color($collectedSum > 0 ? 'warning' : 'gray'),

            Stat::make('Total Collected Transactions', $collectedCount)
                ->descriptionIcon('heroicon-m-document-check')
                ->chart($this->generateChart())
                ->color($collectedCount > 0 ? 'warning' : 'gray'),
            Stat::make('Total Disbursed Amount', $disbursedSum)
                ->descriptionIcon('heroicon-m-document-check')
                ->chart($this->generateChart())
                ->color($disbursedSum > 0 ? 'success' : 'gray'),

            Stat::make('Total Disbursed Transactions', $disbursedCount)
                ->descriptionIcon('heroicon-m-document-check')
                ->chart($this->generateChart())
                ->color($disbursedCount > 0 ? 'success' : 'gray'),


            Stat::make('Total Transactions Amount', $allTransactionSum)
                ->descriptionIcon('heroicon-m-receipt-refund')
                ->chart($this->generateChart())
                ->color($allTransactionSum > 0 ? 'info' : 'gray'),

            Stat::make('Total Failed Amount', $failedSum)
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->chart($this->generateChart())
                ->color($failedSum > 0 ? 'danger' : 'gray'),

            Stat::make('Failed Transactions', $failedCount)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->generateChart())
                ->color($failedCount > 0 ? 'danger' : 'gray'),




        ];
    }

    protected function generateChart($numberOfPoints = 7): array
    {
        // Generate random data points for demonstration (replace with actual data retrieval logic)
        $dataPoints = [];
        for ($i = 0; $i < $numberOfPoints; $i++) {
            $dataPoints[] = rand(1, 20); // Generate random numbers for demonstration
        }

        return $dataPoints;
    }
}
