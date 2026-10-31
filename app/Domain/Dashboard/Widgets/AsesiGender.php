<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Asesi;
use App\Models\User;

class AsesiGender implements Widget
{
    public function key(): string
    {
        return 'asesi_gender';
    }

    public function type(): string
    {
        return 'donut';
    }

    public function title(): string
    {
        return 'Jenis Kelamin Asesi';
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
            ->selectRaw('jenis_kelamin, count(*) as total')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $l = (int) ($rows['L'] ?? 0);
        $p = (int) ($rows['P'] ?? 0);
        $total = $l + $p;

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'asesi',
            categories: ['Laki-laki', 'Perempuan'],
            series: [['name' => 'Asesi', 'data' => [$l, $p]]],
            meta: ['total' => $total, 'laki' => $l, 'perempuan' => $p],
            empty: $total === 0,
        );
    }
}
