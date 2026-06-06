<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tapp\FilamentActivity\Services\ActivityRecorder;

class FilamentActivityServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-activity')
            ->hasConfigFile()
            ->hasMigration('create_activities_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ActivityRecorder::class);
    }
}
