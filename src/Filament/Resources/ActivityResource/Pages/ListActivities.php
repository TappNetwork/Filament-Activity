<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Filament\Resources\ActivityResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Tapp\FilamentActivity\Filament\Resources\ActivityResource;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;
}
