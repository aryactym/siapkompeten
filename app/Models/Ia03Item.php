<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia03Item extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = ['institution_id', 'ia03_asesmen_id', 'urutan', 'pertanyaan', 'tanggapan', 'pencapaian', 'catatan'];

    protected function casts(): array
    {
        return ['pencapaian' => 'boolean'];
    }

    public function ia03(): BelongsTo
    {
        return $this->belongsTo(Ia03Asesmen::class, 'ia03_asesmen_id');
    }
}
