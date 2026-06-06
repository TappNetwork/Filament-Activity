<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $event
 * @property string|null $title
 * @property string|null $summary
 * @property array<string, mixed>|null $metadata
 * @property int|string|null $tenant_id
 * @property Carbon|null $occurred_at
 */
class Activity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForTenant(Builder $query, Model|int|string|null $tenant): Builder
    {
        if ($tenant === null) {
            return $query->whereNull($this->tenantColumn());
        }

        $tenantKey = $tenant instanceof Model ? $tenant->getKey() : $tenant;

        return $query->where($this->tenantColumn(), $tenantKey);
    }

    public function scopeEvent(Builder $query, string|array $event): Builder
    {
        return $query->whereIn('event', (array) $event);
    }

    public function scopeEventPrefix(Builder $query, string|array $prefix): Builder
    {
        return $query->where(function (Builder $query) use ($prefix): void {
            foreach ((array) $prefix as $eventPrefix) {
                $query->orWhere('event', 'like', Str::finish($eventPrefix, '.').'%');
            }
        });
    }

    public function scopeForActor(Builder $query, Model $actor): Builder
    {
        return $query
            ->where('actor_type', $actor->getMorphClass())
            ->where('actor_id', $actor->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForParent(Builder $query, Model $parent): Builder
    {
        return $query
            ->where('parent_type', $parent->getMorphClass())
            ->where('parent_id', $parent->getKey());
    }

    public function scopeOccurredSince(Builder $query, Carbon|string $since): Builder
    {
        return $query->where('occurred_at', '>=', $since);
    }

    public function scopeLatestActivity(Builder $query): Builder
    {
        return $query->latest('occurred_at')->latest($this->getQualifiedKeyName());
    }

    public function tenantColumn(): string
    {
        return config('filament-activity.tenant.foreign_key', 'tenant_id');
    }
}
