<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tapp\FilamentActivity\Models\Activity;

trait HasActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(config('filament-activity.model', Activity::class), 'subject');
    }
}
