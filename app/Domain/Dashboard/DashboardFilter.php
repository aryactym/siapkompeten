<?php

namespace App\Domain\Dashboard;

class DashboardFilter
{
    public function __construct(
        public ?int $tahun = null,
        public ?int $bulanDari = null,
        public ?int $bulanSampai = null,
        public ?string $skemaId = null,
        public ?string $tukId = null,
    ) {
    }

    public static function fromRequest(array $validated): self
    {
        return new self(
            tahun: isset($validated['tahun']) ? (int) $validated['tahun'] : (int) now()->year,
            bulanDari: isset($validated['bulan_dari']) ? (int) $validated['bulan_dari'] : 1,
            bulanSampai: isset($validated['bulan_sampai']) ? (int) $validated['bulan_sampai'] : 12,
            skemaId: $validated['skema_id'] ?? null,
            tukId: $validated['tuk_id'] ?? null,
        );
    }
}
