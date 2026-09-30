<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rich-text (Trix) content as plain text, for card previews and meta descriptions.
        Str::macro('plainText', function (?string $html): string {
            $text = strip_tags(str_replace('<', ' <', (string) $html));

            return Str::squish(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        });
    }
}
