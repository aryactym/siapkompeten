<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia07Item extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = ['institution_id', 'ia07_asesmen_id', 'urutan', 'pertanyaan', 'tanggapan', 'pencapaian', 'catatan'];

    protected function casts(): array
    {
        return ['pencapaian' => 'boolean'];
    }

    public function ia07(): BelongsTo
    {
        return $this->belongsTo(Ia07Asesmen::class, 'ia07_asesmen_id');
    }
}
