<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\JadwalAsesmen;
use App\Models\User;
use Carbon\Carbon;

class Jadwal7Hari implements Widget
{
    public function key(): string
    {
        return 'jadwal_7_hari';
    }

    public function type(): string
    {
        return 'list';
    }

    public function title(): string
    {
        return 'Jadwal 7 Hari ke Depan';
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
        $end = Carbon::now('Asia/Jakarta')->addDays(7)->toDateString();

        $jadwals = JadwalAsesmen::query()
            ->with(['skema', 'tuk'])
            ->whereBetween('tanggal', [now()->toDateString(), $end])
            ->orderBy('tanggal')
            ->limit(10)
            ->get();

        $items = $jadwals->map(fn ($j) => [
            'label' => ($j->skema->nama_skema ?? '—').' · '.($j->tuk->nama_tuk ?? '—'),
            'meta' => $j->tanggal->translatedFormat('d M Y'),
            'href' => '/admin/jadwal',
        ])->all();

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'jadwal',
            list: $items,
            meta: ['total' => count($items)],
            empty: count($items) === 0,
        );
    }
}
