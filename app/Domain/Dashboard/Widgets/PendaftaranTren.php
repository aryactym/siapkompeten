<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\Support\Palette;
use App\Domain\Dashboard\Support\TimeSeries;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Pendaftaran;
use App\Models\User;
use Carbon\Carbon;

class PendaftaranTren implements Widget
{
    public function key(): string
    {
        return 'pendaftaran_tren';
    }

    public function type(): string
    {
        return 'line';
    }

    public function title(): string
    {
        return 'Tren Pendaftaran';
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
        $tahun = $filter->tahun ?? (int) now()->year;

        $tz = 'Asia/Jakarta';
        $start = Carbon::create($tahun, 1, 1, 0, 0, 0, $tz)->utc();
        $end = Carbon::create($tahun, 12, 31, 23, 59, 59, $tz)->utc();

        $rows = Pendaftaran::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'status']);

        $pendaftar = [];
        $terverifikasi = [];
        $terjadwal = [];
        $tersertifikasi = [];

        foreach ($rows as $row) {
            $ym = $row->created_at->format('Y-m');
            $pendaftar[$ym] = ($pendaftar[$ym] ?? 0) + 1;

            if (in_array($row->status, [
                Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
                Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN,
                Pendaftaran::STATUS_MENUNGGU_JADWAL,
                Pendaftaran::STATUS_TERJADWAL,
                Pendaftaran::STATUS_ASESMEN_BERJALAN,
                Pendaftaran::STATUS_SELESAI,
            ], true)) {
                $terverifikasi[$ym] = ($terverifikasi[$ym] ?? 0) + 1;
            }
            if (in_array($row->status, [
                Pendaftaran::STATUS_TERJADWAL,
                Pendaftaran::STATUS_ASESMEN_BERJALAN,
                Pendaftaran::STATUS_SELESAI,
            ], true)) {
                $terjadwal[$ym] = ($terjadwal[$ym] ?? 0) + 1;
            }
            if ($row->status === Pendaftaran::STATUS_SELESAI) {
                $tersertifikasi[$ym] = ($tersertifikasi[$ym] ?? 0) + 1;
            }
        }

        $p = TimeSeries::fillMonths($tahun, $pendaftar);
        $v = TimeSeries::fillMonths($tahun, $terverifikasi);
        $j = TimeSeries::fillMonths($tahun, $terjadwal);
        $s = TimeSeries::fillMonths($tahun, $tersertifikasi);

        $total = array_sum($pendaftar);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'pendaftar',
            categories: $p['categories'],
            series: [
                ['name' => 'Pendaftar', 'data' => $p['data'], 'color' => Palette::color('menunggu')],
                ['name' => 'Terverifikasi', 'data' => $v['data'], 'color' => Palette::color('disetujui')],
                ['name' => 'Terjadwal', 'data' => $j['data'], 'color' => Palette::color('asesmen_lanjut')],
                ['name' => 'Tersertifikasi', 'data' => $s['data'], 'color' => Palette::color('kompeten')],
            ],
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
