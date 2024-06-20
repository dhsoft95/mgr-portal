<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userCount = AppUser::count();
        $verifiedCount = AppUser::where('status', 'verified')->count();
        $unverifiedCount = AppUser::where('status', 'unverified')->count();

        // Placeholder logic for active users, replace with actual queries
        $dailyActiveUsersCount = AppUser::where('created_at', '>=', now()->subDay())->count();
        $monthlyActiveUsersCount = AppUser::where('created_at', '>=', now()->subMonth())->count();
        $yearlyActiveUsersCount = AppUser::where('created_at', '>=', now()->subYear())->count();

        // Count registered users by gender
        $maleCount = AppUser::where('gender', 'male')->count();
        $femaleCount = AppUser::where('gender', 'female')->count();
        $otherCount = AppUser::where('gender', 'other')->count();

        return [
            Stat::make('Total Verified Users', $verifiedCount)
                ->descriptionIcon('heroicon-m-users')
                ->color($verifiedCount > 0 ? 'primary' : 'gray'),
            Stat::make('Total Unverified Users', $unverifiedCount)
                ->descriptionIcon('heroicon-m-users')
                ->color($unverifiedCount > 0 ? 'danger' : 'gray'),
            Stat::make('Total Registered Users', $userCount)
                ->descriptionIcon('heroicon-m-users')
                ->chart($this->generateChart())
                ->color($userCount > 0 ? 'info' : 'gray'),
            Stat::make('Daily Active Users', $dailyActiveUsersCount)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($this->generateChart())
                ->color($dailyActiveUsersCount > 0 ? 'success' : 'gray'),
            Stat::make('Monthly Active Users', $monthlyActiveUsersCount)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($this->generateChart())
                ->color($monthlyActiveUsersCount > 0 ? 'warning' : 'gray'),
            Stat::make('Yearly Active Users', $yearlyActiveUsersCount)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($this->generateChart())
                ->color($yearlyActiveUsersCount > 0 ? 'warning' : 'gray'),
            Stat::make('Total Male Users', $maleCount)
                ->descriptionIcon('heroicon-m-users')
                ->color($maleCount > 0 ? 'primary' : 'gray'),
            Stat::make('Total Female Users', $femaleCount)
                ->descriptionIcon('heroicon-m-users')
                ->color($femaleCount > 0 ? 'primary' : 'gray'),
            Stat::make('Total Other Gender Users', $otherCount)
                ->descriptionIcon('heroicon-m-users')
                ->color($otherCount > 0 ? 'primary' : 'gray')
        ];
    }

    protected function generateChart($numberOfPoints = 7): array
    {
        $dataPoints = [];
        for ($i = 0; $i < $numberOfPoints; $i++) {
            $dataPoints[] = rand(1, 20); // Replace with actual data logic
        }

        return $dataPoints;
    }
}
