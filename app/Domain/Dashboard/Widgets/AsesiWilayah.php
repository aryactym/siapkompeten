<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Asesi;
use App\Models\User;
use Laravolt\Indonesia\Models\Province;

class AsesiWilayah implements Widget
{
    public function key(): string
    {
        return 'asesi_wilayah';
    }

    public function type(): string
    {
        return 'bar';
    }

    public function title(): string
    {
        return 'Top 10 Provinsi Asesi';
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
        $rows = Asesi::query()
            ->selectRaw('provinsi_code, count(*) as total')
            ->whereNotNull('provinsi_code')
            ->groupBy('provinsi_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $provinces = Province::whereIn('code', $rows->pluck('provinsi_code'))->pluck('name', 'code');

        $categories = [];
        $data = [];
        foreach ($rows as $row) {
            $categories[] = $provinces[$row->provinsi_code] ?? $row->provinsi_code;
            $data[] = (int) $row->total;
        }

        $total = array_sum($data);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'asesi',
            categories: $categories,
            series: [['name' => 'Asesi', 'data' => $data, 'color' => 'chart-1']],
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
