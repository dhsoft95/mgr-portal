<?php

namespace App\Providers\Filament;
use App\Filament\Widgets\BlogPostsChart;
use App\Filament\Widgets\CountriesChart;
use App\Filament\Widgets\GeoChartWidget;
use App\Filament\Widgets\GeoMapChart;
use App\Filament\Widgets\GrowthRateChart;
use App\Filament\Widgets\MostTransUserOverview;
use App\Filament\Widgets\MultipleProgressBar;
use App\Filament\Widgets\transactionChart;
use App\Filament\Widgets\TransOverview;
use App\Filament\Widgets\UserChart;
use App\Filament\Widgets\GeoChart;
use App\Filament\Widgets\userinfoChart;
use App\Filament\Widgets\UsersOverview;
use App\Livewire\GeoChartCard;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Brickx\MaintenanceSwitch\MaintenanceSwitchPlugin;
use Chiiya\FilamentAccessControl\FilamentAccessControlPlugin;
use Edwink\FilamentUserActivity\FilamentUserActivityPlugin;
use Filament\Pages\Auth\EditProfile;
use Hydrat\TableLayoutToggle\TableLayoutTogglePlugin;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Saade\FilamentLaravelLog\FilamentLaravelLogPlugin;
use Saade\FilamentLaravelLog\Pages\ViewLog;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/favicon.png'))
             ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->sidebarCollapsibleOnDesktop()->databaseNotifications()
            ->databaseNotificationsPolling('30s')->profile(EditProfile::class)->profile(isSimple: false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])->widgets([
                UsersOverview::class,
                BlogPostsChart::class,
                transactionChart::class,
                MostTransUserOverview::class,
                CountriesChart::class,
                UserChart::class,

            ])->plugins([
                FilamentApexChartsPlugin::make(),
                FilamentProgressbarPlugin::make()->color('yellow'),
//                FilamentUserActivityPlugin::make(),
                FilamentAccessControlPlugin::make(),
                FilamentBackgroundsPlugin::make()->showAttribution(false),
                FilamentLaravelLogPlugin::make()
                    ->navigationGroup('System Tools')
                    ->navigationLabel('Logs')
                    ->navigationIcon('heroicon-o-bug-ant')
                    ->navigationSort(1)
                    ->slug('logs')->viewLog(ViewLog::class),
                LightSwitchPlugin::make()
                    ->position(Alignment::TopRight),

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])->authMiddleware([
                Authenticate::class,
            ]);
    }
}

