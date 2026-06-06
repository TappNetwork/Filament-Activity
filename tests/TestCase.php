<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Tests;

use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tapp\FilamentActivity\FilamentActivityServiceProvider;
use Tapp\FilamentActivity\Tests\Models\Team;
use Tapp\FilamentActivity\Tests\Models\User;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
            FilamentActivityServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('filament-activity.user.model', User::class);
        $app['config']->set('filament-activity.tenant.enabled', true);
        $app['config']->set('filament-activity.tenant.model', Team::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
