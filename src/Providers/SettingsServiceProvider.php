<?php

declare(strict_types=1);

namespace Supplycart\Settings\Providers;

use Illuminate\Support\ServiceProvider;

final class SettingsServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/settings.php', 'settings');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations/create_settings_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_settings_table.php'),
        ], ['settings-migrations', 'migrations']);
    }
}
