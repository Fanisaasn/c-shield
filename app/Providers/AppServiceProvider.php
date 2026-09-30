<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        URL::forceScheme('https');

        Vite::createAssetPathsUsing(function (string $path, ?bool $secure) {
            return '/' . ltrim($path, '/');
        });
    }
}