<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionDocument extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
