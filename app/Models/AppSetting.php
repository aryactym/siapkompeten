<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'key',
        'value',
        'group',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $tenantId = TenantContext::id();

        if ($tenantId !== null) {
            $tenantSetting = static::where('institution_id', $tenantId)
                ->where('key', $key)
                ->value('value');

            if ($tenantSetting !== null) {
                return $tenantSetting;
            }
        }

        $globalSetting = static::whereNull('institution_id')
            ->where('key', $key)
            ->value('value');

        return $globalSetting ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general', bool $isPublic = false): self
    {
        $tenantId = TenantContext::id();

        return static::updateOrCreate(
            [
                'institution_id' => $tenantId,
                'key' => $key,
            ],
            [
                'value' => $value,
                'group' => $group,
                'is_public' => $isPublic,
            ]
        );
    }
}
