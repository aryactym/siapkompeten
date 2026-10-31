<?php

namespace App\Domain\Dashboard\Widgets;

use App\Domain\Dashboard\Contracts\Widget;
use App\Domain\Dashboard\DashboardFilter;
use App\Domain\Dashboard\Support\AgeBucket;
use App\Domain\Dashboard\WidgetResult;
use App\Models\Asesi;
use App\Models\User;
use Carbon\Carbon;

class AsesiUsia implements Widget
{
    public function key(): string
    {
        return 'asesi_usia';
    }

    public function type(): string
    {
        return 'bar';
    }

    public function title(): string
    {
        return 'Kelompok Usia Asesi';
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
        $asasis = Asesi::query()->get(['tanggal_lahir']);
        $buckets = AgeBucket::buckets();
        $today = Carbon::now('Asia/Jakarta');

        foreach ($asasis as $asesi) {
            $age = $asesi->tanggal_lahir ? $asesi->tanggal_lahir->diffInYears($today) : null;
            $buckets[AgeBucket::bucket($age)]++;
        }

        $total = array_sum($buckets);

        return new WidgetResult(
            key: $this->key(),
            type: $this->type(),
            title: $this->title(),
            unit: 'asesi',
            categories: array_keys($buckets),
            series: [['name' => 'Asesi', 'data' => array_values($buckets), 'color' => 'chart-1']],
            meta: ['total' => $total],
            empty: $total === 0,
        );
    }
}
