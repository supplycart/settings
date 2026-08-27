<?php

declare(strict_types=1);

namespace Supplycart\Settings\Tests;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as TestBench;
use RuntimeException;
use Supplycart\Settings\Providers\SettingsServiceProvider;

abstract class TestCase extends TestBench
{
    /** @return list<class-string> */
    #[\Override]
    protected function getPackageProviders($app): array
    {
        return [SettingsServiceProvider::class];
    }

    #[\Override]
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('broadcasting.default', 'null');
    }

    #[\Override]
    protected function defineDatabaseMigrations(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_no')->nullable();
            $table->timestamps();
        });

        $migration = require __DIR__.'/../database/migrations/create_settings_table.php.stub';

        if (! $migration instanceof Migration || ! is_callable([$migration, 'up'])) {
            throw new RuntimeException('The settings migration stub must return an executable Migration instance.');
        }

        call_user_func([$migration, 'up']);
    }
}
