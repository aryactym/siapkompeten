<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\PetaInstrumen;
use App\Models\RencanaAsesmen;
use App\Models\RencanaAsesmenUnit;
use App\Models\Skema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RencanaAsesmenController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RencanaAsesmen::class);

        $asesor = $request->user()->asesor;

        $query = RencanaAsesmen::query()
            ->with(['pendaftaran.skema', 'units.unitKompetensi', 'units.petaInstrumens.kuk'])
            ->when($asesor === null, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($asesor !== null, fn ($q) => $q->where('asesor_id', $asesor->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        $rencanas = $query->paginate(15)->withQueryString();

        return view('asesor.rencana-asesmen.index', [
            'rencanas' => $rencanas,
            'asesor' => $asesor,
        ]);
    }

    public function create(Pendaftaran $pendaftaran): View
    {
        $this->authorize('create', RencanaAsesmen::class);

        $this->authorize('view', $pendaftaran);

        $existing = RencanaAsesmen::where('pendaftaran_id', $pendaftaran->id)->first();

        if ($existing) {
            return redirect()
                ->route('asesor.rencana-asesmen.edit', $existing)
                ->with('info', 'Rencana asesmen untuk pendaftaran ini sudah ada.');
        }

        $skema = $pendaftaran->skema->load(['skemaUnits.unitKompetensi.elemenKompetensis.kriteriaUnjukKerjas']);

        $units = $skema->skemaUnits->map(fn ($su) => $su->unitKompetensi)->sortBy('kode_unit');

        return view('asesor.rencana-asesmen.create', [
            'pendaftaran' => $pendaftaran,
            'skema' => $skema,
            'units' => $units,
        ]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('create', RencanaAsesmen::class);
        $this->authorize('view', $pendaftaran);

        $validated = $request->validate([
            'pendekatan' => ['required', 'string', 'in:observasi,demonstrasi_simulasi,tes_lisan,tes_tertulis,wawancara,portofolio'],
            'tujuan' => ['required', 'string', 'max:2000'],
            'ruang_lingkup' => ['required', 'string', 'max:2000'],
            'tanggal_asesmen' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $asesor = $request->user()->asesor;
        abort_unless($asesor !== null, 403, 'Asesor tidak ditemukan.');

        DB::transaction(function () use ($pendaftaran, $validated, $asesor, $request): void {
            $rencana = RencanaAsesmen::create([
                'institution_id' => $pendaftaran->institution_id,
                'pendaftaran_id' => $pendaftaran->id,
                'asesor_id' => $asesor->id,
                'pendekatan' => $validated['pendekatan'],
                'tujuan' => $validated['tujuan'],
                'ruang_lingkup' => $validated['ruang_lingkup'],
                'tanggal_asesmen' => $validated['tanggal_asesmen'],
                'status' => RencanaAsesmen::STATUS_DRAF,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Buat unit default dari skema jika ada
            $unitIds = $request->input('unit_ids', []);
            if (empty($unitIds)) {
                $skemaUnits = $pendaftaran->skema->skemaUnits;
                foreach ($skemaUnits as $su) {
                    $unitIds[] = $su->unit_kompetensi_id;
                }
            }

            $urutan = 1;
            foreach ($unitIds as $unitId) {
                RencanaAsesmenUnit::create([
                    'institution_id' => $pendaftaran->institution_id,
                    'rencana_asesmen_id' => $rencana->id,
                    'unit_kompetensi_id' => $unitId,
                    'urutan' => $urutan++,
                ]);
            }
        });

        activity('asesmen')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesor membuat rencana asesmen (MAPA.01).');

        return redirect()
            ->route('asesor.rencana-asesmen.edit', RencanaAsesmen::where('pendaftaran_id', $pendaftaran->id)->first())
            ->with('success', 'Rencana asesmen berhasil dibuat. Tambahkan unit kompetensi dan peta instrumen.');
    }

    public function edit(RencanaAsesmen $rencana): View
    {
        $this->authorize('update', $rencana);

        $rencana->load(['pendaftaran.skema.skemaUnits.unitKompetensi', 'units.unitKompetensi.elemenKompetensis.kriteriaUnjukKerjas', 'units.petaInstrumens.kuk']);

        $skema = $rencana->pendaftaran->skema;
        $allUnits = $skema->skemaUnits->map(fn ($su) => $su->unitKompetensi)->sortBy('kode_unit');
        $selectedUnitIds = $rencana->units->pluck('unit_kompetensi_id')->all();

        // KUK untuk semua unit yang tersedia pada skema (digunakan sebagai referensi pemetaan instrumen)
        $kukIds = $allUnits->flatMap(fn ($u) => $u->elemenKompetensis->flatMap(fn ($e) => $e->kriteriaUnjukKerjas->pluck('id')))->unique()->values()->all();
        $kuks = KriteriaUnjukKerja::whereIn('id', $kukIds)->get();

        return view('asesor.rencana-asesmen.edit', [
            'rencana' => $rencana,
            'pendaftaran' => $rencana->pendaftaran,
            'skema' => $skema,
            'allUnits' => $allUnits,
            'selectedUnitIds' => $selectedUnitIds,
            'kuks' => $kuks,
            'kukIds' => $kukIds,
            'metodeOptions' => ['observasi', 'demonstrasi_simulasi', 'tes_lisan', 'tes_tertulis', 'wawancara', 'portofolio'],
        ]);
    }

    public function update(Request $request, RencanaAsesmen $rencana): RedirectResponse
    {
        $this->authorize('update', $rencana);

        abort_if($rencana->isFinal(), 403, 'Rencana yang sudah difinal tidak dapat diubah.');

        $validated = $request->validate([
            'pendekatan' => ['required', 'string', 'in:observasi,demonstrasi_simulasi,tes_lisan,tes_tertulis,wawancara,portofolio'],
            'tujuan' => ['required', 'string', 'max:2000'],
            'ruang_lingkup' => ['required', 'string', 'max:2000'],
            'tanggal_asesmen' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'instrumen_terpilih' => ['nullable', 'array'],
            'instrumen_terpilih.*' => ['string', 'in:ia02,ia03,ia04,ia05,ia06,ia07,ia08,ia09,ia10,ia11'],
            'unit_ids' => ['nullable', 'array'],
            'unit_ids.*' => ['uuid', 'exists:unit_kompetensis,id'],
            'units' => ['nullable', 'array'],
            'units.*.kuks' => ['nullable', 'array'],
            'units.*.kuks.*' => ['uuid', 'exists:kriteria_unjuk_kerjas,id'],
            'units.*.jenis_instrumen' => ['nullable', 'string', 'in:observasi,demonstrasi_simulasi,tes_lisan,tes_tertulis,wawancara,portofolio'],
            'units.*.metode' => ['nullable', 'string', 'in:observasi,demonstrasi_simulasi,tes_lisan,tes_tertulis,wawancara,portofolio'],
            'units.*.bukti' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($rencana, $validated, $request): void {
            $rencana->update([
                'pendekatan' => $validated['pendekatan'],
                'tujuan' => $validated['tujuan'],
                'ruang_lingkup' => $validated['ruang_lingkup'],
                'tanggal_asesmen' => $validated['tanggal_asesmen'],
                'catatan' => $validated['catatan'] ?? null,
                'instrumen_terpilih' => array_values($validated['instrumen_terpilih'] ?? []),
            ]);

            // Sync units
            $selectedUnitIds = $validated['unit_ids'] ?? [];
            $existingUnitIds = $rencana->units->pluck('unit_kompetensi_id')->all();

            $toAdd = array_diff($selectedUnitIds, $existingUnitIds);
            $toRemove = array_diff($existingUnitIds, $selectedUnitIds);

            $urutan = 1;
            foreach ($toAdd as $unitId) {
                RencanaAsesmenUnit::create([
                    'institution_id' => $rencana->institution_id,
                    'rencana_asesmen_id' => $rencana->id,
                    'unit_kompetensi_id' => $unitId,
                    'urutan' => $urutan++,
                ]);
            }

            $rencana->units()->whereIn('unit_kompetensi_id', $toRemove)->delete();

            // Simpan peta instrumen per unit
            $unitsData = $request->input('units', []);
            foreach ($rencana->units as $unit) {
                $unitData = $unitsData[$unit->unit_kompetensi_id] ?? [];
                $kuks = $unitData['kuks'] ?? [];
                $jenisInstrumen = $unitData['jenis_instrumen'] ?? null;
                $metode = $unitData['metode'] ?? null;
                $bukti = $unitData['bukti'] ?? null;

                // Hapus peta lama untuk unit ini
                $unit->petaInstrumens()->delete();

                if (! empty($kuks) && is_array($kuks)) {
                    foreach ($kuks as $kukId) {
                        PetaInstrumen::create([
                            'institution_id' => $rencana->institution_id,
                            'rencana_asesmen_unit_id' => $unit->id,
                            'kuk_id' => $kukId,
                            'jenis_instrumen' => $jenisInstrumen,
                            'metode' => $metode,
                            'bukti' => $bukti,
                            'urutan' => 1,
                        ]);
                    }
                }
            }
        });

        activity('asesmen')
            ->causedBy($request->user())
            ->performedOn($rencana->pendaftaran)
            ->log('Asesor mengubah rencana asesmen (MAPA.01).');

        return back()->with('success', 'Rencana asesmen berhasil diperbarui.');
    }

    public function finalize(Request $request, RencanaAsesmen $rencana): RedirectResponse
    {
        $this->authorize('finalize', $rencana);

        abort_if($rencana->isFinal(), 403, 'Rencana sudah difinal.');

        if ($rencana->units()->count() === 0) {
            return back()->with('error', 'Tidak dapat finalisasi: rencana minimal harus memiliki satu unit kompetensi.');
        }

        $rencana->update(['status' => RencanaAsesmen::STATUS_FINAL]);

        activity('asesmen')
            ->causedBy($request->user())
            ->performedOn($rencana->pendaftaran)
            ->log('Asesor finalisasi rencana asesmen (MAPA.01).');

        return redirect()
            ->route('asesor.rencana-asesmen.edit', $rencana)
            ->with('success', 'Rencana asesmen difinal dan dikunci.');
    }

    public function pdf(RencanaAsesmen $rencana)
    {
        $this->authorize('view', $rencana);

        $rencana->load(['pendaftaran.skema', 'units.unitKompetensi.elemenKompetensis.kriteriaUnjukKerjas', 'units.petaInstrumens.kuk']);

        return Pdf::loadView('pdf.mapa01', [
            'rencana' => $rencana,
            'pendaftaran' => $rencana->pendaftaran,
            'skema' => $rencana->pendaftaran->skema,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('MAPA-01-%s.pdf', $rencana->pendaftaran->no_pendaftaran));
    }

    public function pdfMapa02(RencanaAsesmen $rencana)
    {
        $this->authorize('view', $rencana);

        $rencana->load([
            'pendaftaran.skema',
            'units.unitKompetensi.elemenKompetensis.kriteriaUnjukKerjas',
            'units.petaInstrumens.kuk',
        ]);

        return Pdf::loadView('pdf.mapa02', [
            'rencana' => $rencana,
            'pendaftaran' => $rencana->pendaftaran,
            'skema' => $rencana->pendaftaran->skema,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('MAPA-02-%s.pdf', $rencana->pendaftaran->no_pendaftaran));
    }
}
