<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use BelongsToTenant, HasUuids;

    public const STATUS_VALID = 'valid';

    public const STATUS_INVALID = 'invalid';

    public const STATUS_IMPORTED = 'imported';

    public const STATUS_GAGAL = 'gagal';

    protected $fillable = [
        'institution_id',
        'import_batch_id',
        'row_number',
        'data',
        'errors',
        'status',
        'asesi_id',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'data' => 'array',
            'errors' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function asesi(): BelongsTo
    {
        return $this->belongsTo(Asesi::class);
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function isImported(): bool
    {
        return $this->status === self::STATUS_IMPORTED;
    }
}
