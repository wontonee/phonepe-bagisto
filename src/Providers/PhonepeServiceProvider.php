<?php

namespace Wontonee\Phonepe\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class PhonepeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
        
        // Register PhonePe service as singleton
        $this->app->singleton(\Wontonee\Phonepe\Services\PhonepeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'phonepe');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'phonepe');

        // Publish configuration files
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/wontonee/phonepe'),
        ], 'phonepe-assets');
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/paymentmethods.php',
            'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php',
            'core'
        );
    }
}
