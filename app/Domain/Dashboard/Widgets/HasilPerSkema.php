<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\HasilAsesmen;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HasilPerSkema implements Widget
{
    public function key(): string
    {
        return 'hasil_per_skema';
    }

    public function type(): string
    {
        return 'stacked_bar';
    }

    public function title(): string
    {
        return 'Hasil per Skema';
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
        $rows = DB::table('pendaftarans')
            ->join('skemas', 'skemas.id', '=', 'pendaftarans.skema_id')
            ->leftJoin('hasil_asesmens', 'hasil_asesmens.pendaftaran_id', '=', 'pendaftarans.id')
            ->selectRaw('skemas.nama_skema as skema, hasil_asesmens.rekomendasi, count(*) as total')
            ->groupBy('skemas.nama_skema', 'hasil_asesmens.rekomendasi')
            ->get();

        $skemas = [];
        $kompeten = [];
        $belum = [];
        $lanjut = [];

        foreach ($rows as $row) {
            $name = $row->skema;
            if (! isset($skemas[$name])) {
                $skemas[$name] = true;
                $kompeten[$name] = 0;
                $belum[$name] = 0;
                $lanjut[$name] = 0;
            }
            $r = $row->rekomendasi;
            if ($r === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN) {
                $kompeten[$name] += (int) $row->total;
            } elseif ($r === HasilAsesmen::REKOMENDASI_TIDAK) {
                $belum[$name] += (int) $row->total;
            } else {
                $lanjut[$name] += (int) $row->total;
            }
        }

        // Top 5 by total
        $totals = [];
        foreach ($skemas as $name => $_) {
            $totals[$name] = $kompeten[$name] + $belum[$name] + $lanjut[$name];
        }
        arsort($totals);
        $top = array_slice(array_keys($totals), 0, 5, true);

        $categories = $top;
        $k = array_map(fn ($n) => $kompeten[$n], $top);
        $b = array_map(fn ($n) => $belum[$n], $top);
        $l = array_map(fn ($n) => $lanjut[$n], $top);

        $total = array_sum($totals);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'pendaftar',
            categories: $categories,
            series: [
                ['name' => 'Kompeten', 'data' => $k, 'color' => 'chart-1'],
                ['name' => 'Belum Kompeten', 'data' => $b, 'color' => 'chart-2'],
                ['name' => 'Asesmen Lanjut', 'data' => $l, 'color' => 'chart-3'],
            ],
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
