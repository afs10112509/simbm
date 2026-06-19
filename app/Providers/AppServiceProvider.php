<?php

namespace App\Providers;

use App\Support\NavigationGroups;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Carbon\Carbon;
use Filament\Events\ServingFilament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        App::setLocale('id');
        Carbon::setLocale('id');

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['id']);
        });

        Event::listen(ServingFilament::class, function (): void {
            if (! auth()->check()) {
                return;
            }

            $groups = collect(NavigationGroups::forCurrentUser())
                ->mapWithKeys(fn (string $label) => [
                    $label => NavigationGroup::make()->label($label),
                ])
                ->all();

            filament()->getCurrentPanel()?->navigationGroups($groups);
        });
    }
}
