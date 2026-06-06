<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

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

    public function tenantColumn(): string
    {
        return config('filament-activity.tenant.foreign_key', 'tenant_id');
    }
}
