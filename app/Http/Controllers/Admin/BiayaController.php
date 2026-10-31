<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Biaya;
use App\Models\Skema;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BiayaController extends Controller
{
    public function index(Request $request): View
    {
        $skemaId = $request->query('skema');

        $skemas = Skema::query()->orderBy('nama_skema')->get();

        $biayas = Biaya::query()
            ->with('skema')
            ->when($skemaId, fn ($query) => $query->where('skema_id', $skemaId))
            ->orderBy('jenis_biaya')
            ->get();

        return view('admin.biaya.index', [
            'biayas' => $biayas,
            'skemas' => $skemas,
            'skemaTerpilih' => $skemas->firstWhere('id', $skemaId),
            'newBiaya' => new Biaya([
                'skema_id' => $skemaId,
                'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBiaya($request);

        $biaya = Biaya::create($validated);

        activity('biaya')
            ->causedBy($request->user())
            ->performedOn($biaya)
            ->log('Biaya skema ditambahkan.');

        return redirect()
            ->route('admin.biaya.index', ['skema' => $biaya->skema_id])
            ->with('success', 'Biaya skema berhasil ditambahkan.');
    }

    public function update(Request $request, Biaya $biaya): RedirectResponse
    {
        $biaya->update($this->validateBiaya($request, $biaya));

        activity('biaya')
            ->causedBy($request->user())
            ->performedOn($biaya)
            ->log('Biaya skema diperbarui.');

        return redirect()
            ->route('admin.biaya.index', ['skema' => $biaya->skema_id])
            ->with('success', 'Biaya skema berhasil diperbarui.');
    }

    public function destroy(Request $request, Biaya $biaya): RedirectResponse
    {
        $biaya->delete();

        activity('biaya')
            ->causedBy($request->user())
            ->performedOn($biaya)
            ->log('Biaya skema dihapus.');

        return redirect()
            ->route('admin.biaya.index')
            ->with('success', 'Biaya skema berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBiaya(Request $request, ?Biaya $biaya = null): array
    {
        // Normalisasi input berformat (FR-PAY-06): buang pemisah ribuan sebelum divalidasi.
        if ($request->has('nominal')) {
            $request->merge([
                'nominal' => preg_replace('/\D/', '', (string) $request->input('nominal')),
            ]);
        }

        $validated = $request->validate([
            'skema_id' => [
                'required',
                'uuid',
                Rule::exists('skemas', 'id')->where('institution_id', TenantContext::id()),
            ],
            'skkni_id' => ['nullable', 'uuid'],
            'jenis_biaya' => ['required', 'string', 'max:80'],
            'nominal' => ['required', 'integer', 'min:0', 'max:999999999999999'],
            'tanggung_jawab' => ['required', Rule::in([Biaya::TANGGUNG_PESERTA, Biaya::TANGGUNG_FASILITATOR])],
        ]);

        $unique = Biaya::query()
            ->where('skema_id', $validated['skema_id'])
            ->where('jenis_biaya', $validated['jenis_biaya'])
            ->when($biaya !== null, fn ($query) => $query->whereKeyNot($biaya->getKey()));

        if ($unique->exists()) {
            throw ValidationException::withMessages([
                'jenis_biaya' => 'Jenis biaya untuk skema ini sudah ada.',
            ]);
        }

        return $validated;
    }
}
