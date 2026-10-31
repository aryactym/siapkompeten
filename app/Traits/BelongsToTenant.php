<?php

namespace App\Traits;

use App\Models\Institution;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Partisi data per lembaga (tenant) berbasis kolom institution_id.
 *
 * - Query otomatis difilter institution_id dari session aktif (fail closed).
 * - institution_id diisi otomatis saat creating.
 * - institution_id tidak boleh diubah setelah record dibuat.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $institutionId = TenantContext::id();

            if ($institutionId === null) {
                // Fail closed: tanpa konteks tenant, jangan bocorkan data lembaga mana pun.
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('institution_id'),
                $institutionId
            );
        });

        static::creating(function (Model $model): void {
            $contextId = TenantContext::id();
            $modelId = $model->getAttribute('institution_id');

            if (blank($modelId)) {
                if ($contextId === null) {
                    throw new LogicException(
                        'Tidak dapat menyimpan data tenant tanpa institution_id atau session lembaga aktif.'
                    );
                }

                $model->setAttribute('institution_id', $contextId);

                return;
            }

            if ($contextId !== null && $modelId !== $contextId) {
                throw new LogicException('Penulisan lintas lembaga ditolak.');
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('institution_id')) {
                throw new LogicException('institution_id tidak boleh diubah setelah data dibuat.');
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function scopeForInstitution(Builder $query, string $institutionId): Builder
    {
        return $query
            ->withoutGlobalScope('tenant')
            ->where($this->qualifyColumn('institution_id'), $institutionId);
    }

    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
