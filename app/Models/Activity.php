<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    use HasUuids;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (Activity $activity): void {
            if (empty($activity->institution_id)) {
                $activity->institution_id = TenantContext::id();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder): void {
            $institutionId = TenantContext::id();
            if ($institutionId !== null) {
                $builder->where(
                    $builder->getModel()->qualifyColumn('institution_id'),
                    $institutionId
                );
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
