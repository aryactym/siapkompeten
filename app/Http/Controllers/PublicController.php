<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\CmsContent;
use App\Models\Institution;
use App\Models\Skema;
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

        $beritas = CmsContent::withoutTenantScope()
            ->where('kategori', 'berita')
            ->where('is_active', true)
            ->when($institution, fn ($q) => $q->where('institution_id', $institution->id))
            ->orderByDesc('tanggal_terbit')
            ->limit(6)
            ->get();

        return view('public.index', [
            'institution' => $institution,
            'skemas' => $skemas,
            'asesors' => $asesors,
            'beritas' => $beritas,
        ]);
    }
}
