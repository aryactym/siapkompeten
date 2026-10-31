<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\JadwalAsesmen;
use App\Models\User;

class JadwalKeterisian implements Widget
{
    public function key(): string
    {
        return 'jadwal_keterisian';
    }

    public function type(): string
    {
        return 'bar';
    }

    public function title(): string
    {
        return 'Keterisian Jadwal Mendatang';
    }

    public function roles(): array
    {
        return ['admin', 'pimpinan'];
    }

    public function authorize(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('pimpinan');
    }

    public function ttl(): int
    {
        return 300;
    }

    public function data(DashboardFilter $filter): WidgetResult
    {
        $jadwals = JadwalAsesmen::query()
            ->withCount('pendaftarans')
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->limit(8)
            ->get();

        $categories = [];
        $kuota = [];
        $terisi = [];

        foreach ($jadwals as $j) {
            $categories[] = $j->tanggal->translatedFormat('d M');
            $kuota[] = (int) $j->kuota;
            $terisi[] = (int) $j->pendaftarans_count;
        }

        $total = array_sum($terisi);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'peserta',
            categories: $categories,
            series: [
                ['name' => 'Kuota', 'data' => $kuota, 'color' => 'chart-4'],
                ['name' => 'Terisi', 'data' => $terisi, 'color' => 'chart-1'],
            ],
            meta: ['total' => $total],
            empty: $jadwals->isEmpty(),
        );
    }
}
