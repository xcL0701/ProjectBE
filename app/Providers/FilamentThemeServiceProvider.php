<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;

class FilamentThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'head.start',
            fn (): string => '<link rel="icon" type="image/png" href="' . asset('favicon.png') . '">'
        );
    }
}

