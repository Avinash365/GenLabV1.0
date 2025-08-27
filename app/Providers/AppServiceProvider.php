<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            $settings = \App\Models\SiteSetting::query()->first();
            $appSettings = [
                'site_logo_url' => ($settings && $settings->site_logo) ? asset('storage/' . $settings->site_logo) : null,
                'theme_color' => $settings->theme_color ?? '#4B9CFF',
                'company_name' => $settings->company_name ?? null,
                'company_address' => $settings->company_address ?? null,
            ];
        } catch (\Throwable $e) {
            $appSettings = [];
        }

        view()->share('appSettings', $appSettings);
    }
}
