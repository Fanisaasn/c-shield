<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Paksa HTTPS hanya di server (Railway); di lokal `php artisan serve` hanya melayani HTTP.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Vite::createAssetPathsUsing(function (string $path, ?bool $secure) {
            return '/' . ltrim($path, '/');
        });

        // Ubah deskripsi HTML (dari editor Trix) menjadi teks biasa untuk ringkasan/meta.
        Str::macro('plainText', function (?string $html): string {
            $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</div>', '</p>', '</li>'], ' ', (string) $html));

            return Str::squish(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        });
    }
}