<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Services;

use Illuminate\Database\Eloquent\Model;
use Tapp\FilamentActivity\Models\Activity;

class ActivityRecorder
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $event,
        Model $subject,
        ?Model $actor = null,
        ?Model $tenant = null,
        ?Model $parent = null,
        ?string $title = null,
        ?string $summary = null,
        array $metadata = [],
    ): Activity {
        $activityModel = config('filament-activity.model', Activity::class);
        $tenantColumn = config('filament-activity.tenant.foreign_key', 'tenant_id');

        /** @var Activity $activity */
        $activity = new $activityModel;
        $activity->forceFill([
            'event' => $event,
            'title' => $title,
            'summary' => $summary,
            'metadata' => $metadata,
            $tenantColumn => $tenant?->getKey(),
            'occurred_at' => now(),
        ]);

        $activity->subject()->associate($subject);

        if ($actor) {
            $activity->actor()->associate($actor);
        }

        if ($parent) {
            $activity->parent()->associate($parent);
        }

        $activity->save();

        return $activity;
    }
}
