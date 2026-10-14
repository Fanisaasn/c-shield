<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, ?string $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get the full URL for the configured application logo, or null if using default.
     */
    public static function getLogoUrl(): ?string
    {
        $value = static::getValue('app_logo');
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    }

    /**
     * Get the configured navbar background color, default is #0b2545.
     */
    public static function getNavbarColor(): string
    {
        return static::getValue('navbar_color', '#0b2545') ?: '#0b2545';
    }

    /**
     * Get the configured navbar text and links color, default is #ffffff.
     */
    public static function getNavbarTextColor(): string
    {
        return static::getValue('navbar_text_color', '#ffffff') ?: '#ffffff';
    }

    /**
     * Get the configured dashboard background color, default is #f8fafc.
     */
    public static function getBgColor(): string
    {
        return static::getValue('bg_color', '#f8fafc') ?: '#f8fafc';
    }

    /**
     * Get the configured primary button background color, default is #2ab7ca.
     */
    public static function getBtnColor(): string
    {
        return static::getValue('btn_color', '#2ab7ca') ?: '#2ab7ca';
    }

    /**
     * Get the configured primary button text color, default is #0b2545.
     */
    public static function getBtnTextColor(): string
    {
        return static::getValue('btn_text_color', '#0b2545') ?: '#0b2545';
    }

    /**
     * Get the configured application/logo brand name, default is C-SHIELD.
     */
    public static function getAppName(): string
    {
        return static::getValue('app_name', 'C-SHIELD') ?: 'C-SHIELD';
    }

    /**
     * Get the configured hero badge text, default is Diskominfo Kota Cimahi.
     */
    public static function getHeroBadge(): string
    {
        return static::getValue('hero_badge', 'Diskominfo Kota Cimahi') ?: 'Diskominfo Kota Cimahi';
    }

    /**
     * Get the configured hero title heading.
     */
    public static function getHeroTitle(): string
    {
        return static::getValue('hero_title', 'Cimahi Cyber Security Hub & Awareness Field') ?: 'Cimahi Cyber Security Hub & Awareness Field';
    }

    /**
     * Get the configured hero description text.
     */
    public static function getHeroDescription(): string
    {
        $default = 'Pusat informasi dan edukasi keamanan siber untuk masyarakat dan aparatur Kota Cimahi. Temukan artikel, video, flyer, webinar, serta self-assessment untuk membantu meningkatkan kesadaran dan keamanan digital.';

        return static::getValue('hero_description', $default) ?: $default;
    }

    /**
     * Get the configured hero button call to action text.
     */
    public static function getHeroBtnText(): string
    {
        return static::getValue('hero_btn_text', 'Mulai Self Assessment') ?: 'Mulai Self Assessment';
    }
}
