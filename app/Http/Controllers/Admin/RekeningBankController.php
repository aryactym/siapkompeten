<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekeningBank;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RekeningBankController extends Controller
{
    public function index(): View
    {
        $rekeningBanks = RekeningBank::query()
            ->orderByDesc('is_aktif')
            ->orderBy('nama_bank')
            ->get();

        return view('admin.rekening.index', [
            'rekeningBanks' => $rekeningBanks,
            'newRekeningBank' => new RekeningBank(['is_aktif' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rekeningBank = RekeningBank::create($this->validateRekening($request));

        activity('rekening_bank')
            ->causedBy($request->user())
            ->performedOn($rekeningBank)
            ->log('Rekening bank LSP ditambahkan.');

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function update(Request $request, RekeningBank $rekeningBank): RedirectResponse
    {
        $rekeningBank->update($this->validateRekening($request, $rekeningBank));

        activity('rekening_bank')
            ->causedBy($request->user())
            ->performedOn($rekeningBank)
            ->log('Rekening bank LSP diperbarui.');

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function destroy(Request $request, RekeningBank $rekeningBank): RedirectResponse
    {
        if ($rekeningBank->pembayarans()->exists()) {
            return back()->with('error', 'Rekening masih digunakan oleh riwayat pembayaran dan tidak dapat dihapus.');
        }

        $rekeningBank->delete();

        activity('rekening_bank')
            ->causedBy($request->user())
            ->performedOn($rekeningBank)
            ->log('Rekening bank LSP dihapus.');

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening bank berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRekening(Request $request, ?RekeningBank $rekeningBank = null): array
    {
        $rules = [
            'nama_bank' => ['required', 'string', 'max:60'],
            'no_rekening' => ['required', 'string', 'max:40', 'regex:/^[0-9\-\s]+$/'],
            'atas_nama' => ['required', 'string', 'max:120'],
            'is_aktif' => ['sometimes', 'boolean'],
        ];

        $rules['no_rekening'][] = Rule::unique('rekening_banks', 'no_rekening')
            ->where('institution_id', TenantContext::id())
            ->ignore($rekeningBank?->getKey());

        $validated = $request->validate($rules);

        $validated['is_aktif'] = (bool) ($validated['is_aktif'] ?? false);

        return $validated;
    }
}
