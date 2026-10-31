<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Pendaftaran;
use App\Models\User;

class AntreanTindakan implements Widget
{
    public function key(): string
    {
        return 'antrean_tindakan';
    }

    public function type(): string
    {
        return 'list';
    }

    public function title(): string
    {
        return 'Antrean Tindakan';
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
        $items = [];
        $items[] = ['label' => 'Dokumen menunggu verifikasi', 'count' => Pendaftaran::where('status', Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)->count(), 'href' => '/admin/pendaftaran?tab=perlu_verifikasi'];
        $items[] = ['label' => 'Pembayaran menunggu validasi', 'count' => Pendaftaran::where('status', Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN)->count(), 'href' => '/admin/pembayaran'];
        $items[] = ['label' => 'Siap plotting jadwal', 'count' => Pendaftaran::where('status', Pendaftaran::STATUS_MENUNGGU_JADWAL)->count(), 'href' => '/admin/pendaftaran?tab=siap_plotting'];

        $total = array_sum(array_column($items, 'count'));

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'item',
            list: $items,
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
