<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\CmsContent;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\Skema;
use App\Models\Tuk;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function index(): View
    {
        $institution = Institution::query()->where('is_active', true)->orderBy('name')->first();

        $skemas = Skema::withoutTenantScope()
            ->where('is_active', true)
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->orderBy('nama_skema')
            ->get();

        $asesors = Asesor::withoutTenantScope()
            ->where('status', 'aktif')
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->with('user')
            ->orderBy('id')
            ->get();

        $jadwals = JadwalAsesmen::withoutTenantScope()
            ->where('status', 'terbuka')
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->with(['skema', 'tuk'])
            ->orderBy('tanggal')
            ->limit(6)
            ->get();

        $tuks = Tuk::withoutTenantScope()
            ->where('is_active', true)
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->orderBy('nama_tuk')
            ->get();

        $cms = CmsContent::withoutTenantScope()
            ->where('is_active', true)
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->orderBy('urutan')
            ->orderByDesc('tanggal_terbit')
            ->get()
            ->groupBy('kategori');

        return view('public.index', [
            'institution' => $institution,
            'skemas' => $skemas,
            'asesors' => $asesors,
            'jadwals' => $jadwals,
            'tuks' => $tuks,
            'cms' => $cms,
        ]);
    }
}
