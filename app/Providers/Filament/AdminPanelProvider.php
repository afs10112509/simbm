<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel

            /*
            |--------------------------------------------------------------------------
            | Panel
            |--------------------------------------------------------------------------
            */

            ->default()

            ->id('admin')

            ->path('admin')

            ->login()

            ->brandName(fn (): string => \App\Support\AppSettings::appName())

            ->brandLogo(fn (): string => \App\Support\PwaIconGenerator::transparentLogoUrl())

            ->brandLogoHeight('3rem')

            ->favicon(fn (): string => \App\Support\PwaIconGenerator::iconUrl(192))

            ->sidebarCollapsibleOnDesktop()

            ->maxContentWidth(MaxWidth::Full)

            ->renderHook(
                'panels::head.end',
                fn (): string => \App\Support\PwaManifest::headHtml(),
            )

            ->renderHook(
                'panels::auth.login.form.after',
                fn () => view('filament.auth.login-info'),
            )

            /*
            |--------------------------------------------------------------------------
            | Warna
            |--------------------------------------------------------------------------
            */

            ->colors([
                'primary' => Color::Amber,
            ])

            ->font('Inter')

            ->navigationGroups([
                'Keuangan',
                'Konter',
                'Bengkel',
                'Administrasi',
            ])

            /*
            |--------------------------------------------------------------------------
            | Resources
            |--------------------------------------------------------------------------
            */

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            /*
            |--------------------------------------------------------------------------
            | Pages
            |--------------------------------------------------------------------------
            */

            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )

            /*
            |--------------------------------------------------------------------------
            | Widgets
            |--------------------------------------------------------------------------
            */

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

            ->widgets([
                \App\Filament\Widgets\OwnerBusinessOverview::class,
                \App\Filament\Widgets\FinanceStats::class,
                \App\Filament\Widgets\AccountBalanceStats::class,
                \App\Filament\Widgets\CashflowChart::class,
                \App\Filament\Widgets\BranchBalanceStats::class,
                \App\Filament\Widgets\RecentTransactions::class,
                \App\Filament\Widgets\UpahKerjaStats::class,
                \App\Filament\Widgets\UpahKerjaRecent::class,
                \App\Filament\Widgets\SpecializedInputStats::class,
                \App\Filament\Widgets\SpecializedRecentTransactions::class,
                \App\Filament\Widgets\ServiceStats::class,
            ])

            /*
            |--------------------------------------------------------------------------
            | Middleware
            |--------------------------------------------------------------------------
            */

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

            ])

            /*
            |--------------------------------------------------------------------------
            | Auth Middleware
            |--------------------------------------------------------------------------
            */

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}