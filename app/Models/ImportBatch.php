<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use BelongsToTenant, HasUuids;

    public const STATUS_PREVIEW = 'preview';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'institution_id',
        'user_id',
        'original_name',
        'file_path',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'imported_rows',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'imported_rows' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class)->orderBy('row_number');
    }

    public function asesis(): HasMany
    {
        return $this->hasMany(Asesi::class);
    }

    public function scopePreview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PREVIEW);
    }

    public function isPreview(): bool
    {
        return $this->status === self::STATUS_PREVIEW;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    public function hasErrors(): bool
    {
        return $this->invalid_rows > 0;
    }
}
