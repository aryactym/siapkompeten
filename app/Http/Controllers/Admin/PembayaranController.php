<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Services\BuktiBayarStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');

        $pembayarans = Pembayaran::query()
            ->with(['pendaftaran.asesi.user', 'pendaftaran.skema', 'rekeningBank'])
            ->when(
                $status !== 'semua',
                fn ($query) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pembayaran.index', [
            'pembayarans' => $pembayarans,
            'statusAktif' => $status,
        ]);
    }

    public function show(Pembayaran $pembayaran): View
    {
        $pembayaran->load(['pendaftaran.asesi.user', 'pendaftaran.skema', 'rekeningBank', 'divalidasiOleh']);

        return view('admin.pembayaran.show', [
            'pembayaran' => $pembayaran,
        ]);
    }

    /**
     * Unduh bukti bayar dari disk privat (A01/A08).
     */
    public function bukti(Pembayaran $pembayaran, BuktiBayarStorage $storage): BinaryFileResponse|StreamedResponse
    {
        abort_unless($storage->exists($pembayaran->bukti_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk(BuktiBayarStorage::DISK)->download(
            $pembayaran->bukti_path,
            $pembayaran->bukti_original_name ?: 'bukti-pembayaran'
        );
    }

    /**
     * Validasi pembayaran: asesi lanjut ke tahap plotting atau kembali memperbaiki
     * bukti bayar (FR-PAY-05).
     */
    public function putuskan(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([Pembayaran::STATUS_VALID, Pembayaran::STATUS_TIDAK_VALID])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === Pembayaran::STATUS_TIDAK_VALID && blank($validated['catatan'] ?? null)) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan wajib diisi saat pembayaran ditandai tidak valid.',
            ]);
        }

        if ($pembayaran->status === Pembayaran::STATUS_VALID) {
            return back()->with('error', 'Pembayaran sudah divalidasi sebelumnya.');
        }

        DB::transaction(function () use ($pembayaran, $validated, $request): void {
            $pendaftaran = Pendaftaran::withoutTenantScope()
                ->whereKey($pembayaran->pendaftaran_id)
                ->lockForUpdate()
                ->firstOrFail();

            $pembayaran->update([
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'divalidasi_oleh' => $request->user()->id,
                'divalidasi_pada' => now(),
            ]);

            $pendaftaran->update([
                'status' => $validated['status'] === Pembayaran::STATUS_VALID
                    ? Pendaftaran::STATUS_MENUNGGU_JADWAL
                    : Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
            ]);

            activity('pembayaran')
                ->causedBy($request->user())
                ->performedOn($pembayaran)
                ->withProperties(['status' => $validated['status']])
                ->log('Pembayaran divalidasi oleh admin.');
        });

        $pesan = $validated['status'] === Pembayaran::STATUS_VALID
            ? 'Pembayaran valid. Pendaftaran masuk ke tahap penjadwalan.'
            : 'Pembayaran ditandai tidak valid. Asesi diminta mengunggah bukti bayar baru.';

        return redirect()
            ->route('admin.pembayaran.show', $pembayaran)
            ->with('success', $pesan);
    }
}
