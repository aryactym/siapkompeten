<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\RekeningBank;
use App\Services\BuktiBayarStorage;
use App\Support\TenantContext;
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

        $rekeningBanks = RekeningBank::query()
            ->where('is_aktif', true)
            ->orderBy('nama_bank')
            ->get();

        return view('admin.pembayaran.show', [
            'pembayaran' => $pembayaran,
            'rekeningBanks' => $rekeningBanks,
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

    /**
     * Admin input bukti pembayaran manual dan langsung validasi sebagai valid (FR-PAY-05).
     * Digunakan saat asesi membayar tunai/transfer tanpa mengunggah bukti sendiri.
     */
    public function inputManual(Request $request, Pendaftaran $pendaftaran, BuktiBayarStorage $storage): RedirectResponse
    {
        abort_unless(
            in_array($pendaftaran->status, [
                Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
                Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN,
            ], true),
            403,
            'Pendaftaran tidak berada pada tahap pembayaran.'
        );

        $validated = $request->validate([
            'rekening_bank_id' => [
                'required',
                'uuid',
                Rule::exists('rekening_banks', 'id')
                    ->where('institution_id', TenantContext::id())
                    ->where('is_aktif', true),
            ],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'bukti' => [
                'nullable',
                'file',
                'mimes:'.implode(',', config('pendaftaran.document_mimes')),
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:'.config('pendaftaran.document_max_size_kb'),
            ],
        ]);

        $path = null;
        if ($request->hasFile('bukti') && $request->file('bukti')->isValid()) {
            $path = $storage->store($pendaftaran, $request->file('bukti'));
        }

        $pembayaran = null;

        DB::transaction(function () use ($pendaftaran, $validated, $path, $request, &$pembayaran): void {
            $oldPath = null;

            $pembayaran = Pembayaran::firstOrNew(['pendaftaran_id' => $pendaftaran->id]);
            $oldPath = $pembayaran->bukti_path;

            $pembayaran->fill([
                'total' => $pendaftaran->totalTagihan(),
                'rekening_bank_id' => $validated['rekening_bank_id'],
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'catatan' => $validated['catatan'] ?? null,
                'status' => Pembayaran::STATUS_VALID,
                'divalidasi_oleh' => $request->user()->id,
                'divalidasi_pada' => now(),
            ]);

            if ($path !== null) {
                $pembayaran->fill([
                    'bukti_path' => $path,
                    'bukti_original_name' => $request->file('bukti')->getClientOriginalName(),
                    'bukti_mime' => $request->file('bukti')->getMimeType(),
                    'bukti_size' => $request->file('bukti')->getSize(),
                ]);
            }

            $pembayaran->save();

            $pendaftaran->update(['status' => Pendaftaran::STATUS_MENUNGGU_JADWAL]);

            activity('pembayaran')
                ->causedBy($request->user())
                ->performedOn($pendaftaran)
                ->withProperties(['input_manual' => true])
                ->log('Admin menginput pembayaran manual dan memvalidasi langsung.');

            // Hapus bukti lama jika diganti
            if (filled($oldPath) && $path !== null && $oldPath !== $path) {
                $storage->delete($oldPath);
            }
        });

        return redirect()
            ->route('admin.pembayaran.show', $pembayaran)
            ->with('success', 'Pembayaran berhasil diinput. Pendaftaran masuk ke tahap penjadwalan.');
    }
}
