<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlenoPeserta extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const KEPUTUSAN_KOMPETEN = 'kompeten';

    public const KEPUTUSAN_BELUM_KOMPETEN = 'belum_kompeten';

    protected $fillable = [
        'institution_id',
        'pleno_id',
        'pendaftaran_id',
        'keputusan',
        'catatan',
    ];

    public function pleno(): BelongsTo
    {
        return $this->belongsTo(Pleno::class);
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }
}
