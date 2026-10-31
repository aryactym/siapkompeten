<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Pendaftaran;
use App\Models\User;

class PendaftaranFunnel implements Widget
{
    public function key(): string
    {
        return 'pendaftaran_funnel';
    }

    public function type(): string
    {
        return 'bar';
    }

    public function title(): string
    {
        return 'Funnel Pendaftaran';
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
        $counts = Pendaftaran::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $get = fn (array $statuses) => array_sum(array_map(fn ($s) => (int) ($counts[$s] ?? 0), $statuses));

        $stages = [
            'Dokumen' => [Pendaftaran::STATUS_DRAF, Pendaftaran::STATUS_DOKUMEN_DIAJUKAN, Pendaftaran::STATUS_DOKUMEN_DITOLAK],
            'Verifikasi' => [Pendaftaran::STATUS_DOKUMEN_DIAJUKAN],
            'Pembayaran' => [Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN, Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN],
            'Penjadwalan' => [Pendaftaran::STATUS_MENUNGGU_JADWAL],
            'Terjadwal' => [Pendaftaran::STATUS_TERJADWAL, Pendaftaran::STATUS_ASESMEN_BERJALAN],
            'Selesai' => [Pendaftaran::STATUS_SELESAI],
        ];

        $categories = array_keys($stages);
        $data = array_map($get, array_values($stages));
        $total = array_sum($data);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'pendaftar',
            categories: $categories,
            series: [['name' => 'Jumlah', 'data' => $data, 'color' => 'chart-1']],
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
