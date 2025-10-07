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
        
        // Register License service as singleton
        $this->app->singleton(\Wontonee\Phonepe\Services\LicenseService::class);
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
            __DIR__ . '/../Config/paymentmethods.php' => config_path('paymentmethods.php'),
        ], 'phonepe-config');

        // Publish system configuration
        $this->publishes([
            __DIR__ . '/../Config/system.php' => config_path('phonepe-system.php'),
        ], 'phonepe-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/phonepe'),
        ], 'phonepe-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../Resources/lang' => lang_path('vendor/phonepe'),
        ], 'phonepe-lang');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/wontonee/phonepe'),
        ], 'phonepe-assets');

        // Publish everything
        $this->publishes([
            __DIR__ . '/../Config/paymentmethods.php' => config_path('paymentmethods.php'),
            __DIR__ . '/../Config/system.php' => config_path('phonepe-system.php'),
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/phonepe'),
            __DIR__ . '/../Resources/lang' => lang_path('vendor/phonepe'),
            __DIR__ . '/../Resources/assets' => public_path('vendor/wontonee/phonepe'),
        ], 'phonepe');
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
